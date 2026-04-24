<h1>勤務編集</h1>

<form method="POST" action="/attendance/update/{{ $attendance->id }}">
    @csrf

    <label>出勤</label>
    <input type="time" name="clock_in"
        value="{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}">

    <br>

    <label>退勤</label>
    <input type="time" name="clock_out"
        value="{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}">

    <br>

    <button type="submit">保存</button>
</form>