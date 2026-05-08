<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>勤怠管理</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef2f7;
            padding: 30px;
            max-width: 1200px;
            margin: auto;
            color: #333;
        }

        .container {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .left {
            width: 320px;
        }

        .right {
            flex: 1;
            background: white;
            padding: 24px;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 14px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .status-text {
            font-size: 40px;
            font-weight: bold;
            margin-top: 10px;
        }

        .actions form {
            margin-bottom: 10px;
        }

        button {
            padding: 12px;
            width: 100%;
            border: none;
            border-radius: 8px;
            background-color: #4caf50;
            color: white;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
            transition: 0.2s;
        }

        button:hover {
            transform: translateY(-1px);
            opacity: 0.95;
        }

        .clock-out {
            background-color: #f44336;
        }

        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th {
            background-color: #2f3640;
            color: white;
            position: sticky;
            top: 0;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
        }

        tr:hover {
            background-color: #f9fafb;
        }

        .today {
            background-color: #e8f4ff;
            font-weight: bold;
        }

        a {
            color: #2563eb;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        @media screen and (max-width: 900px) {
            .container {
                flex-direction: column;
            }

            .left {
                width: 100%;
            }
        }

        .status-working {
            color: #16a34a;
        }

        .status-finished {
            color: #dc2626;
        }

        .status-wait {
            color: #555;
        }
    </style>
</head>

<body>

    <h1>勤怠管理システム</h1>

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
                <h2 class="status-text status-working">出勤中</h2>
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
            <div style="
                display:flex;
                justify-content:space-between;
                align-items:20px;
                margin-bottom:20px;
                background:white;
                padding:12px 20px;
                border-radius:10px;
                ">
                <a href="{{ route('attendance.index', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}">
                    ←前月
                </a>

                <h2>{{ $month->format('Y年m月') }}</h2>

                <a href="{{ route('attendance.index', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}">
                    次月→
                </a>
            </div>

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