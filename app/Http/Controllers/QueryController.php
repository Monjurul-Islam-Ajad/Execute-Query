<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QueryController extends Controller
{

    /**
     * @throws \Throwable
     */
    public function executeQuery(Request $request)
    {
        $mysqlQuery = $request->input('query');
        $executionLog = [];
        if (!empty($mysqlQuery)) {
            try {
                DB::connection('mysql')->beginTransaction();
                DB::connection('pgsql')->beginTransaction();
                $mysqlQuery = explode(";", $mysqlQuery);
                foreach ($mysqlQuery as $query) {
                    $query = trim($query);
                    if (!empty($query)) {
                        try {
                            DB::connection('mysql')->statement($query);
                            $executionLog['mysql'][] = [
                                'query' => $query,
                                'status' => 'Success',
                                'message' => 'MySQL query executed successfully.'
                            ];
                        } catch (Exception $mysqlException) {
                            $executionLog['mysql'][] = [
                                'query' => $query,
                                'status' => 'Failed',
                                'message' => $mysqlException->getMessage()
                            ];
                            DB::connection('mysql')->rollBack();
                        }
                    }
                }

                $pgsqlQueries = $this->convertToPgSQL($mysqlQuery);
                foreach ($pgsqlQueries as $pgsqlQuery) {
                    $pgsqlQuery = trim($pgsqlQuery);
                    if (!empty($pgsqlQuery)) {
                        try {
                            DB::connection('pgsql')->statement($pgsqlQuery);
                            $executionLog['pgsql'][] = [
                                'query' => $pgsqlQuery,
                                'status' => 'Success',
                                'message' => 'PostgreSQL query executed successfully.'
                            ];
                        } catch (Exception $pgsqlException) {
                            $executionLog['pgsql'][] = [
                                'query' => $pgsqlQuery,
                                'status' => 'Failed',
                                'message' => $pgsqlException->getMessage()
                            ];
                        }
                    }
                }

                DB::connection('mysql')->commit();
                DB::connection('pgsql')->commit();


            } catch (Exception $e) {
                DB::connection('mysql')->rollBack();
                DB::connection('pgsql')->rollBack();
                $executionLog = $e;
            }
        }
        return redirect()->back()->with('executionLog', $executionLog)->withInput();

    }

    private function convertToPgSQL($mysqlQuery)
    {
        // Basic conversions (can be expanded based on your requirements)
        $pgsqlQuery = str_replace('`', '"', $mysqlQuery); // Convert backticks to double quotes
        // Convert MySQL query to PostgreSQL-compatible query
        $pgsqlQuerys = str_ireplace('AUTO_INCREMENT', 'SERIAL', $pgsqlQuery); // Convert AUTO_INCREMENT to SERIAL

// Replace INT with INTEGER
        $pgsqlQuerys = str_ireplace(' INT ', ' INTEGER ', $pgsqlQuerys);

// Replace TINYINT(1) with BOOLEAN
        $pgsqlQuerys = str_ireplace(' TINYINT(1)', ' BOOLEAN', $pgsqlQuerys);

// Replace DATETIME with TIMESTAMP
        $pgsqlQuerys = str_ireplace(' DATETIME', ' TIMESTAMP', $pgsqlQuerys);

// Replace FLOAT with REAL
        $pgsqlQuerys = str_ireplace(' FLOAT', ' REAL', $pgsqlQuerys);

// Replace DOUBLE with DOUBLE PRECISION
        $pgsqlQuerys = str_ireplace(' DOUBLE', ' DOUBLE PRECISION', $pgsqlQuerys);

// Convert backticks to double quotes (optional, depends on usage)
        $pgsqlQuerys = str_ireplace('`', '"', $pgsqlQuerys);

// Replace ENGINE=InnoDB with nothing, as PostgreSQL does not use this
        $pgsqlQuerys = str_ireplace(' ENGINE=InnoDB', '', $pgsqlQuerys);

// Handle primary key and unique constraints
        $pgsqlQuerys = preg_replace('/PRIMARY KEY \((.*?)\)/i', 'PRIMARY KEY ($1)', $pgsqlQuerys);
        $pgsqlQuerys = preg_replace('/UNIQUE \((.*?)\)/i', 'UNIQUE ($1)', $pgsqlQuerys);

// Replace default values with PostgreSQL syntax
        $pgsqlQuerys = preg_replace('/DEFAULT (.*?)$/i', 'DEFAULT $1', $pgsqlQuerys);

// Handle other types or constraints as needed
// Example: Convert ENUM to a CHECK constraint
        $pgsqlQuerys = str_ireplace('ENUM(', 'CHECK(', $pgsqlQuerys);

// Return the converted query
        return $pgsqlQuerys;

        // Add more conversions here as needed for your queries

        return $pgsqlQuerys;
    }
}
