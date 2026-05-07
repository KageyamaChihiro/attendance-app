<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>勤怠管理</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f6fa;
            padding: 20px;
            max-width: 900px;
            margin: auto;
        }

        h1 {
            margin-bottom: 20px;
            text-align: center;
        }

        .container {
            display: flex;
            gap: 20px;
        }

        .left {
            width: 35%;
        }

        .right {
            width: 65%;
            background: white;
            padding: 20px;
            border-radius: 10px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .status {
            text-align: center;
        }

        .status-text {
            font-size: 36px;
            font-weight: bold;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .today {
            background-color: #e3f2fd;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #f0f0f0;
        }

        button {
            padding: 10px;
            width: 100%;
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

        button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>

<body>

    <h1 style="margin-bottom: 20px;">勤怠管理</h1>

    {{-- メッセージ --}}
    @if(session('success'))
    <p style="color: green;">{{ session('success') }}</p>
    @endif

    @if(session('error'))
    <p style="color: red;">{{ session('error') }}</p>
    @endif

    <div class="container">

        <!-- 左 -->
        <div class="left">

            <!-- 状態 -->
            <div class="card status">
                <h2>現在の状態</h2>

                @if($todayAttendance && $todayAttendance->clock_in && !$todayAttendance->clock_out)
                <h2 class="status-text" style="color: green;">出勤中</h2>
                @elseif($todayAttendance && $todayAttendance->clock_out)
                <h2 class="status-text" style="color: red;">退勤済</h2>
                @else
                <h2 class="status-text">未出勤</h2>
                @endif
            </div>

            <!-- 打刻 -->

            <div class="card">
                <h3>合計勤務時間</h3>
                <p style="font-size: 20px; font-weight: bold; margin-top: 10px;">
                    {{ floor($totalMinutes /60)}}時間 {{$totalMinutes % 60}}分
                </p>
            </div>
            <div class="card actions">
                <h2>打刻</h2>

                <form method="POST" action="{{ route('clock.in') }}">
                    @csrf
                    <button
                        @if($todayAttendance && $todayAttendance->clock_in) disabled @endif>
                        出勤
                    </button>
                </form>

                @if($todayAttendance && $todayAttendance->clock_in)
                <form method="POST" action="/break-update">
                    @csrf
                    <label>休憩時間(分)</label>
                    <input type="number" name="break_time" value="{{ $todayAttendance->break_time ?? 60 }}" min="0" max="180">
                    <button type="submit">更新</button>
                    <p>休憩時間:{{ $todayAttendance->break_time ?? 60 }}分</p>
                </form>
                @endif

                <form method="POST" action="{{ route('clock.out') }}">
                    @csrf
                    <button class="clock-out"
                        @if(!$todayAttendance || $todayAttendance->clock_out) disabled @endif>
                        退勤
                    </button>
                </form>
            </div>

        </div>

        <!-- 右 -->
        <div class="right">

            <h2>勤怠一覧</h2>

            <table>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>勤務区分</th>
                    <th>予定時間</th>
                    <th>実働時間</th>
                    <th>残業時間</th>
                    <th>状態</th>
                    <th>操作</th>
                </tr>
                
                @foreach($attendances as $attendance)
                <tr class="{{ $attendance->work_date == now()->toDateString() ? 'today' : '' }}">
                    <td>{{ $attendance->work_date }}</td>

                    <td>
                        {{ $attendance->clock_in
                        ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
                        : '-' }}
                    </td>

                    <td>
                        {{ $attendance->clock_out
                        ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
                        : '-' }}
                    </td>

                    <td>{{ $attendance->break_time ?? 0}}分</td>

                    <td>{{ $attendance->work_type }}</td>

                    <td>
                        {{ $attendance->scheduled_start ?? '-' }}～{{ $attendance->scheduled_end ?? '-'}}
                    </td>

                    <td>
                        @if($attendance->work_type === '有給')
                        有給
                        @elseif($attendance->clock_in && $attendance->clock_out)
                        @php
                        $workMinutes = \Carbon\Carbon::parse($attendance->clock_in)
                        ->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out));

                        $actualMinutes = max(0, $workMinutes - ($attendance->break_time ?? 0));

                        $hours = floor($actualMinutes /60);
                        $minutes = $actualMinutes % 60;
                        @endphp

                        {{ $hours }}時間 {{ $minutes}}分
                        @else
                        -
                        @endif
                    </td>
                    <td>
                        @if(
                            $attendance->clock_out &&
                            $attendance->scheduled_end &&
                            $attendance->work_type !== '有給'
                        )
                        @php
                        $overtimeMinutes = \Carbon\Carbon::parse($attendance->scheduled_end)
                        ->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out),
                                        false);
                        $overtimeMinutes = max(0, $overtimeMinutes);
                        $overtimeHours = floor($overtimeMinutes /60);
                        $overtimeMinutes = $overtimeMinutes % 60;
                        @endphp

                        {{ $overtimeHours }}時間 {{ $overtimeMinutes }}分

                        @else

                        -

                        @endif
                    </td>
                    
                    <td>
                        @if($attendance->clock_in && !$attendance->clock_out)
                        出勤中
                        @elseif($attendance->clock_out)
                        退勤済
                        @else
                        未出勤
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('attendance.edit', $attendance->id) }}">
                            編集
                        </a>
                    </td>
                </tr>
                @endforeach
            </table>

        </div>

    </div>

</body>

</html>