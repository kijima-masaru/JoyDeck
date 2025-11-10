<?php

namespace App\Services;

class SwitchCommandService
{
    // Switchコントローラーのボタン定義
    const BUTTON_A = 'A';
    const BUTTON_B = 'B';
    const BUTTON_X = 'X';
    const BUTTON_Y = 'Y';
    const BUTTON_L = 'L';
    const BUTTON_R = 'R';
    const BUTTON_ZL = 'ZL';
    const BUTTON_ZR = 'ZR';
    const BUTTON_PLUS = 'PLUS';
    const BUTTON_MINUS = 'MINUS';
    const BUTTON_HOME = 'HOME';
    const BUTTON_CAPTURE = 'CAPTURE';
    const BUTTON_UP = 'UP';
    const BUTTON_DOWN = 'DOWN';
    const BUTTON_LEFT = 'LEFT';
    const BUTTON_RIGHT = 'RIGHT';
    const BUTTON_L_STICK_CLICK = 'L_STICK_CLICK';
    const BUTTON_R_STICK_CLICK = 'R_STICK_CLICK';

    // 有効なボタンリスト
    protected $validButtons = [
        self::BUTTON_A,
        self::BUTTON_B,
        self::BUTTON_X,
        self::BUTTON_Y,
        self::BUTTON_L,
        self::BUTTON_R,
        self::BUTTON_ZL,
        self::BUTTON_ZR,
        self::BUTTON_PLUS,
        self::BUTTON_MINUS,
        self::BUTTON_HOME,
        self::BUTTON_CAPTURE,
        self::BUTTON_UP,
        self::BUTTON_DOWN,
        self::BUTTON_LEFT,
        self::BUTTON_RIGHT,
        self::BUTTON_L_STICK_CLICK,
        self::BUTTON_R_STICK_CLICK,
    ];

    /**
     * Switchコマンドを作成
     * 
     * フォーマット例:
     * - ボタン押下: "BUTTON:A:PRESS"
     * - ボタン解放: "BUTTON:A:RELEASE"
     * - 方向キー: "DPAD:UP:PRESS"
     * - スティック: "STICK:L:CLICK:PRESS"
     */
    public function createCommand(string $button, bool $pressed): string
    {
        if (!in_array($button, $this->validButtons)) {
            throw new \InvalidArgumentException("Invalid button: {$button}");
        }

        $action = $pressed ? 'PRESS' : 'RELEASE';

        // 方向キーの場合
        if (in_array($button, [self::BUTTON_UP, self::BUTTON_DOWN, self::BUTTON_LEFT, self::BUTTON_RIGHT])) {
            return "DPAD:{$button}:{$action}";
        }

        // スティッククリックの場合
        if (in_array($button, [self::BUTTON_L_STICK_CLICK, self::BUTTON_R_STICK_CLICK])) {
            $stick = $button === self::BUTTON_L_STICK_CLICK ? 'L' : 'R';
            return "STICK:{$stick}:CLICK:{$action}";
        }

        // 通常のボタンの場合
        return "BUTTON:{$button}:{$action}";
    }

    /**
     * 複数のボタンを同時に送信するコマンドを作成
     */
    public function createMultiCommand(array $buttons, bool $pressed): string
    {
        $commands = [];
        foreach ($buttons as $button) {
            if (in_array($button, $this->validButtons)) {
                $commands[] = $this->createCommand($button, $pressed);
            }
        }
        
        return implode('|', $commands);
    }

    /**
     * スティックの位置を設定するコマンドを作成
     * 
     * @param string $stick 'L' または 'R'
     * @param int $x X軸の値 (-32767 ～ 32767)
     * @param int $y Y軸の値 (-32767 ～ 32767)
     */
    public function createStickCommand(string $stick, int $x, int $y): string
    {
        if (!in_array($stick, ['L', 'R'])) {
            throw new \InvalidArgumentException("Invalid stick: {$stick}");
        }

        // 値を範囲内に制限
        $x = max(-32767, min(32767, $x));
        $y = max(-32767, min(32767, $y));

        return "STICK:{$stick}:{$x}:{$y}";
    }

    /**
     * 有効なボタンリストを取得
     */
    public function getValidButtons(): array
    {
        return $this->validButtons;
    }
}

