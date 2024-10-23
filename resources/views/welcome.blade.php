<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QUERY Execution</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .failed {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1>Database Execution</h1>
    <div class="flex justify-center pt-8 sm:justify-start sm:pt-0">
        <form action="{{url('execute-query')}}" method="post">
            @csrf
            <textarea class="form-control" name="query" style="height: 400px">{{old('query')}}</textarea>
            <input type="submit" class="form-control">

        </form>
    </div>
    <h2>MySQL Logs</h2>
    @if (session('executionLog'))
        @php
            $executionLog = session('executionLog');
        @endphp
    @endif
    @if (!empty($executionLog['mysql']))
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Query</th>
                <th>Status</th>
                <th>Message</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($executionLog['mysql'] as $log)
                <tr class="{{ $log['status'] === 'Success' ? 'success' : 'failed' }}">
                    <td>{{ $log['query'] }}</td>
                    <td>{{ $log['status'] }}</td>
                    <td>{{ $log['message'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p>No MySQL logs available.</p>
    @endif

    <h2>PostgreSQL Logs</h2>
    @if (!empty($executionLog['pgsql']))
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Query</th>
                <th>Status</th>
                <th>Message</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($executionLog['pgsql'] as $log)
                <tr class="{{ $log['status'] === 'Success' ? 'success' : 'failed' }}">
                    <td>{{ $log['query'] }}</td>
                    <td>{{ $log['status'] }}</td>
                    <td>{{ $log['message'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p>No PostgreSQL logs available.</p>
    @endif
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
