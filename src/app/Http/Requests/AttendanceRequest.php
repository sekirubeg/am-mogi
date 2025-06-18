<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
            'clock_in' => ['nullable', 'date_format:H:i'],
            'clock_out' => ['nullable', 'date_format:H:i', 'after_or_equal:clock_in'],
            'breaks.*.break_start' => ['nullable', 'date_format:H:i', 'after_or_equal:clock_in'],
            'breaks.*.break_end' => ['nullable', 'date_format:H:i', 'after_or_equal:breaks.*.break_start', 'before_or_equal:clock_out'],
            'new_breaks.*.break_start' => ['nullable', 'date_format:H:i', 'after_or_equal:clock_in'],
            'new_breaks.*.break_end' => ['nullable', 'date_format:H:i', 'after_or_equal:new_breaks.*.break_start', 'before_or_equal:clock_out'],
            'remarks' => ['required'],
        ];
    }
    public function messages()
    {
        return [
            'clock_in.date_format' => '出勤時間はHH:MM形式で入力してください',
            'clock_out.date_format' => '退勤時間はHH:MM形式で入力してください',
            'clock_out.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',
            'breaks.*.break_start.date_format' => '休憩開始時間はHH:MM形式で入力してください',
            'breaks.*.break_start.after_or_equal' => '休憩時間が勤務時間外です',
            'breaks.*.break_end.date_format' => '休憩終了時間はHH:MM形式で入力してください',
            'breaks.*.break_end.after_or_equal' => '開始時間より後に設定してください',
            'breaks.*.break_end.before_or_equal' => '休憩時間が勤務時間外です',
            'new_breaks.*.break_start.date_format' => '新しい休憩開始時間はHH:MM形式で入力してください',
            'new_breaks.*.break_start.after_or_equal' => '休憩時間が勤務時間外です',
            'new_breaks.*.break_end.date_format' => '新しい休憩終了時間はHH:MM形式で入力してください',
            'new_breaks.*.break_end.after_or_equal' => '開始時間より後に設定してください',
            'new_breaks.*.break_end.before_or_equal' => '休憩時間が勤務時間外です',
            'remarks.required' => '備考を記入してください',
        ];
    }
}
