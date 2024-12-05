<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;

class ChatController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $message = $request->message;

        $systemInstruction = '
            Bạn là một trợ lý quản lý tài chính cá nhân.

            Một giao dịch tài chính bao gồm 2 loại: Chi và Thu.

            Các loại Chi bao gồm: ' . implode(', ', config('assisstant.category.expense')) . '
            Các loại Thu bao gồm: ' . implode(', ', config('assisstant.category.income')) . '

            Bạn có thể nhận các giao dịch dưới dạng văn bản và trả về thông tin chi tiết của giao dịch đó theo cú pháp sau dưới dạng JSON:

            ```
            {
                "type": "Chi",
                "category": "Ăn uống",
                "amount": 100000,
                "note": "Ăn sáng tại quán cơm"
            }
            ```

            Hãy chuyển đổi câu văn sau thành thông tin giao dịch tài chính:

            ```
            ' . $message . '
            ```

            Lưu ý:

            - Câu trả lời chỉ bao gồm thông tin của giao dịch dưới dạng JSON (không bao gồm phần Markdown), không cần in ra các thông báo khác.
            - Một số đơn vị đặc biệt: 1k = 1000, 1tr = 1000000.
        ';

        $result = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'user', 'content' => $systemInstruction],
            ],
        ]);
        
        $response = $result->choices[0]->message->content;
        $response = json_decode($response, true);

        $expense = Expense::create($response);

        return response()->json($expense);
    }
}
