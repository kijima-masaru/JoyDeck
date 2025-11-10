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
}

