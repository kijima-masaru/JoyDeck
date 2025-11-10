<?php

namespace App\Services;

class SwitchCommandService
{
    // Switchコントローラーのボタン定義
    const BUTTON_A = 'A';
    const BUTTON_B = 'B';
    const BUTTON_X = 'X';
    const BUTTON_Y = 'Y';
    const BUTTON_L1 = 'L1';
    const BUTTON_L2 = 'L2';
    const BUTTON_L3 = 'L3';
    const BUTTON_R1 = 'R1';
    const BUTTON_R2 = 'R2';
    const BUTTON_R3 = 'R3';
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
        self::BUTTON_L1,
        self::BUTTON_L2,
        self::BUTTON_L3,
        self::BUTTON_R1,
        self::BUTTON_R2,
        self::BUTTON_R3,
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
     * ボタン名を内部的なボタン名に変換
     * L1→L, R1→R, L2→ZL, R2→ZR
     */
    protected function normalizeButtonName(string $button): string
    {
        $mapping = [
            self::BUTTON_L1 => 'L',
            self::BUTTON_R1 => 'R',
            self::BUTTON_L2 => 'ZL',
            self::BUTTON_R2 => 'ZR',
        ];

        return $mapping[$button] ?? $button;
    }

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

        // L1/L2/R1/R2をL/R/ZL/ZRに変換
        $normalizedButton = $this->normalizeButtonName($button);

        // 通常のボタンの場合
        return "BUTTON:{$normalizedButton}:{$action}";
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

    /**
     * キーボード入力コマンドを作成
     * 
     * フォーマット例:
     * - 文字入力: "KEYBOARD:CHAR:a"
     * - 特殊キー: "KEYBOARD:KEY:ENTER"
     * - バックスペース: "KEYBOARD:KEY:BACKSPACE"
     * 
     * @param string $char 入力する文字（1文字）
     * @return string
     */
    public function createKeyboardCharCommand(string $char): string
    {
        if (strlen($char) !== 1) {
            throw new \InvalidArgumentException("Character must be exactly 1 character");
        }

        return "KEYBOARD:CHAR:" . $char;
    }

    /**
     * キーボード特殊キーコマンドを作成
     * 
     * @param string $key 特殊キー（ENTER, BACKSPACE, TAB, ESC, etc.）
     * @return string
     */
    public function createKeyboardKeyCommand(string $key): string
    {
        $validKeys = [
            'ENTER', 'BACKSPACE', 'TAB', 'ESC', 'SPACE',
            'DELETE', 'HOME', 'END', 'PAGEUP', 'PAGEDOWN',
            'ARROW_UP', 'ARROW_DOWN', 'ARROW_LEFT', 'ARROW_RIGHT'
        ];

        if (!in_array($key, $validKeys)) {
            throw new \InvalidArgumentException("Invalid keyboard key: {$key}");
        }

        return "KEYBOARD:KEY:" . $key;
    }

    /**
     * 文字列をキーボード入力コマンドに変換
     * 
     * @param string $text 入力する文字列
     * @return array コマンドの配列
     */
    public function createKeyboardTextCommands(string $text): array
    {
        $commands = [];
        $length = mb_strlen($text, 'UTF-8');
        
        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($text, $i, 1, 'UTF-8');
            $commands[] = $this->createKeyboardCharCommand($char);
        }
        
        return $commands;
    }
}

