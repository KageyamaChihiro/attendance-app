<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>勤怠管理</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f6fa;
            padding: 20px;
        }
        h1 {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #f0f0f0;
        }
        button {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            background-color: #4caf50;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .clock-out {
            background-color: #f44336;
        }
        .clock-out:hover {
            background-color: #da190b;
        }
    </style>
</head>
<body>
    <h1>勤怠一覧</h1>

    <form method="POST" action="/clock-in" style="display: inline;">
        @csrf
        <button type="submit">出勤</button>
    </form>

    <form method="POST" action="/clock-out" style="display: inline;">
        @csrf
        <button type="submit" class="clock-out">退勤</button>
    </form>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>日付</th>
            <th>状態</th>
        </tr>
        @foreach($attendances as $attendance)
        <tr>
            <td>{{ $attendance['id'] }}</td>
            <td>{{ $attendance['date'] }}</td>
            <td>{{ $attendance['status'] }}</td>
        </tr>
        @endforeach
    </table>
</body>
</html>