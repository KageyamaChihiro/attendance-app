<h1>勤務編集</h1>

<form method="POST" action="{{ route('attendance.update', $attendance->id) }}">
    @csrf
    @method('PUT')

    <label>出勤</label>
    <input type="datetime-local" name="clock_in"
        value="{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('Y-m-d\TH:i') : '' }}">

    <br>

    <label>退勤</label>
    <input type="datetime-local" name="clock_out"
        value="{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('Y-m-d\TH:i') : '' }}">

    <br>

    <button type="submit">保存</button>
</form>