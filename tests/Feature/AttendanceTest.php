<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_clock_in_creates_attendance()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->post('/clock-in');
        $response->assertRedirect();
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_cannot_clock_in_twice()
    {
        Attendance::create([
            'work_date' => now()->toDateString(),
            'clock_in' => now(),
        ]);
        $response = $this->post('/clock-in');
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_clock_out_updates_attendance()
    {
        Attendance::create([
            'work_date' => now()->toDateString(),
            'clock_in' =>now(),
        ]);
        $response = $this->post('/clock-out');
        $response->assertSessionHas('success');
        $attendance = Attendance::first();
        $this->assertNotNull($attendance->clock_out);
    }

    public function test_cannot_clock_out_without_clock_in()
    {
        $response = $this->post('/clock-out');
        $response->assertSessionHas('error');
    }

    public function test_break_time_can_be_updated()
    {
        Attendance::create([
            'work_date' => now()->toDateString(),
            'clock_in' => now(),
        ]);
        $response = $this->post('/break-update', [
            'break_time' =>  60,
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('attendances', [
            'break_time' => 60,
        ]);
    }

    public function test_work_type_can_be_updated()
    {
        $attendance = Attendance::create([
            'work_date' => now()->toDateString(),
        ]);
        $response = $this->put("/attendance/{$attendance->id}", [
            'work_date' => now()->toDateString(),
            'work_type' => '有給',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'work_type' =>'有給',
        ]);
    }

    public function test_attendance_index_page_is_displayed()
    {
        $response = $this->get('/attendance');
        $response->assertStatus(200);
    }

    public function test_break_time_cannot_be_negative()
    {
        Attendance::create([
            'work_date' => now()->toDateString(),
        ]);
        $response = $this->post('/break-update', [
            'break_time' => -1,
        ]);
        $response->assertSessionHasErrors('break_time');
    }

    public function test_break_time_cannot_exceed_180_minutes()
    {
        Attendance::create([
            'work_date' => now()->toDateString(),
        ]);
        $response = $this->post('/break-update', [
            'break_time' => 181,
        ]);
        $response->assertSessionHasErrors('break_time');
    }

    public function test_clock_out_must_be_after_clock_in()
    {
        $attendance = Attendance::create([
            'work_date' => now()->toDateString(),
        ]);
        $response = $this->put("/attendance/{$attendance->id}", [
            'work_date' => now()->toDateString(),
            'clock_in' => '18:00',
            'clock_out' => '09:00',
        ]);
        $response->assertSessionHasErrors('clock_out');
    }
}
