<h1>勤務編集</h1>

<form method="POST" action="{{ route('attendance.update', $attendance->id) }}">
    @csrf
    @method('PUT')

    <label>勤怠区分</label>
    <select name="work_type" id="work_type">
        <option value="通常" {{ $attendance->work_type == '通常' ? 'selected' : '' }}>通常</option>
        <option value="有給" {{ $attendance->work_type == '有給' ? 'selected' : '' }}>有給</option>
        <option value="休出" {{ $attendance->work_type == '休出' ? 'selected' : '' }}>休出</option>
    </select>
    
    <br><br>

    <div id="time-fields">

    <label>出勤</label>
    <input type="datetime-local" name="clock_in"
        value="{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('Y-m-d\TH:i') : '' }}">
    <br><br>

    <label>退勤</label>
    <input type="datetime-local" name="clock_out"
        value="{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('Y-m-d\TH:i') : '' }}">
    <br><br>

    <label>予定開始</label>
    <input type="time" name="scheduled_start" value="{{ $attendance->scheduled_start }}">

    <br><br>

    <label>予定終了</label>
    <input type="time" name="scheduled_end" value="{{ $attendance->scheduled_end }}">

    </div>
    <br><br>

    <button type="submit">保存</button>
</form>

<script>
    const workType = document.getElementById('work_type');
    const timeFields = document.getElementById('time-fields');

    function toggleTimeFields() {
        if (workType.value === '有給') {
            timeFields.style.display = 'none';
        } else {
            timeFields.style.display = 'block';
        }
    }
    toggleTimeFields();
    workType.addEventListener('change', toggleTimeFields);
</script>