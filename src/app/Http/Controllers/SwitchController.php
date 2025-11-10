<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\MicrocontrollerService;
use App\Services\SwitchCommandService;

class SwitchController extends Controller
{
    protected $microcontrollerService;
    protected $switchCommandService;

    public function __construct(
        MicrocontrollerService $microcontrollerService,
        SwitchCommandService $switchCommandService
    ) {
        $this->microcontrollerService = $microcontrollerService;
        $this->switchCommandService = $switchCommandService;
    }

    /**
     * Switchコントローラーページを表示
     */
    public function index()
    {
        return view('controller');
    }

    /**
     * マイコンに接続
     */
    public function connect(Request $request): JsonResponse
    {
        try {
            $connected = $this->microcontrollerService->connect();
            
            if ($connected) {
                return response()->json([
                    'success' => true,
                    'message' => 'マイコンに接続しました'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'マイコンに接続できませんでした'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * マイコンから切断
     */
    public function disconnect(Request $request): JsonResponse
    {
        try {
            $this->microcontrollerService->disconnect();
            
            return response()->json([
                'success' => true,
                'message' => 'マイコンから切断しました'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Switchコマンドを送信
     */
    public function sendCommand(Request $request): JsonResponse
    {
        $request->validate([
            'button' => 'required|string',
            'pressed' => 'required|boolean'
        ]);

        try {
            $button = $request->input('button');
            $pressed = $request->input('pressed');

            $command = $this->switchCommandService->createCommand($button, $pressed);
            $sent = $this->microcontrollerService->sendCommand($command);

            if ($sent) {
                return response()->json([
                    'success' => true,
                    'message' => "コマンドを送信しました: {$button} " . ($pressed ? 'ON' : 'OFF')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'コマンドの送信に失敗しました'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * マイコンの接続状態を取得
     */
    public function getStatus(): JsonResponse
    {
        $isConnected = $this->microcontrollerService->isConnected();
        
        return response()->json([
            'connected' => $isConnected
        ]);
    }

    /**
     * キーボード入力を送信（通常入力モード用）
     */
    public function sendKeyboardInput(Request $request): JsonResponse
    {
        $request->validate([
            'char' => 'nullable|string|max:1',
            'key' => 'nullable|string',
            'text' => 'nullable|string|max:1000'
        ]);

        try {
            // 文字列が指定されている場合は、文字列全体を送信
            if ($request->has('text') && !empty($request->input('text'))) {
                $text = $request->input('text');
                $commands = $this->switchCommandService->createKeyboardTextCommands($text);
                
                foreach ($commands as $command) {
                    $this->microcontrollerService->sendCommand($command);
                    // 少し遅延を入れて、入力が正しく処理されるようにする
                    usleep(10000); // 10ms
                }
                
                return response()->json([
                    'success' => true,
                    'message' => "文字列を送信しました: " . mb_substr($text, 0, 20) . (mb_strlen($text) > 20 ? '...' : '')
                ]);
            }
            
            // 1文字が指定されている場合
            if ($request->has('char') && !empty($request->input('char'))) {
                $char = $request->input('char');
                $command = $this->switchCommandService->createKeyboardCharCommand($char);
                $sent = $this->microcontrollerService->sendCommand($command);
                
                if ($sent) {
                    return response()->json([
                        'success' => true,
                        'message' => "文字を送信しました: {$char}"
                    ]);
                }
            }
            
            // 特殊キーが指定されている場合
            if ($request->has('key') && !empty($request->input('key'))) {
                $key = $request->input('key');
                $command = $this->switchCommandService->createKeyboardKeyCommand($key);
                $sent = $this->microcontrollerService->sendCommand($command);
                
                if ($sent) {
                    return response()->json([
                        'success' => true,
                        'message' => "キーを送信しました: {$key}"
                    ]);
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => 'char、key、またはtextのいずれかを指定してください'
            ], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

