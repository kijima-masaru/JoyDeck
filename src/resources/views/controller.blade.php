<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>JoyDeck - Switch コントローラー</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 1200px;
            width: 100%;
            border: 2px solid black;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }

        .status {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .status.connected {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status.disconnected {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .accordion {
            margin-bottom: 30px;
        }

        .accordion-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            user-select: none;
        }

        .accordion-header:hover {
            background: #e9ecef;
            border-color: #667eea;
        }

        .accordion-header h3 {
            margin: 0;
            color: #333;
            font-size: 18px;
        }

        .accordion-icon {
            font-size: 16px;
            transition: transform 0.3s;
        }

        .accordion-icon.open {
            transform: rotate(180deg);
        }

        .accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .accordion-content.open {
            max-height: 2000px;
            transition: max-height 0.5s ease-in;
        }

        .key-mapping {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 15px;
            padding-top: 15px;
        }

        .key-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px solid transparent;
        }

        .key-item.active {
            border-color: #667eea;
            background: #e7f3ff;
        }

        .key-label {
            font-weight: bold;
            color: #333;
        }

        .key-value {
            color: #666;
            font-family: 'Courier New', monospace;
            padding: 4px 8px;
            background: white;
            border-radius: 4px;
            min-width: 80px;
            text-align: center;
        }

        .button-group {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        button {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
        }

        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .log {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            max-height: 400px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            margin-top: 0;
        }

        .log-entry {
            margin-bottom: 5px;
            color: #333;
        }

        .log-entry.error {
            color: #dc3545;
        }

        .log-entry.success {
            color: #28a745;
        }

        /* キーボードビジュアル */
        .keyboard-container {
            background: #2c3e50;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .keyboard {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-width: 100%;
            margin: 0 auto;
            overflow-x: auto;
        }

        .keyboard-row {
            display: flex;
            gap: 6px;
            justify-content: center;
            flex-wrap: nowrap;
        }

        .key {
            background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
            border: 2px solid #1a252f;
            border-radius: 6px;
            padding: 12px 16px;
            width: 50px;
            min-width: 50px;
            max-width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);
            transition: all 0.1s ease;
            position: relative;
            user-select: none;
        }

        .key.mapped {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #5568d3;
            box-shadow: 0 0 15px rgba(102, 126, 234, 0.5);
        }

        .key.pressed {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            border-color: #d68910;
            transform: translateY(2px);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        .key.mapped.pressed {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            border-color: #d68910;
            box-shadow: 0 0 20px rgba(243, 156, 18, 0.8);
        }

        .key-label {
            position: relative;
            z-index: 1;
        }

        .key-switch-label {
            position: absolute;
            bottom: 4px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 9px;
            color: rgba(255, 255, 255, 0.7);
            font-weight: normal;
        }

        /* 特殊キーのサイズ調整 */
        .key.tab { width: 70px; min-width: 70px; max-width: 70px; }
        .key.caps { width: 85px; min-width: 85px; max-width: 85px; }
        .key.shift { width: 100px; min-width: 100px; max-width: 100px; }
        .key.ctrl { width: 70px; min-width: 70px; max-width: 70px; }
        .key.alt { width: 70px; min-width: 70px; max-width: 70px; }
        .key.space { width: 300px; min-width: 300px; max-width: 300px; }
        .key.enter { width: 100px; min-width: 100px; max-width: 100px; }
        .key.backspace { width: 100px; min-width: 100px; max-width: 100px; }

        .keyboard-section {
            margin-bottom: 20px;
        }

        .keyboard-section-title {
            color: rgba(255, 255, 255, 0.7);
            font-size: 12px;
            margin-bottom: 10px;
            text-align: center;
        }

        /* 設定モーダル */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            overflow-y: auto;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            max-width: 900px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }

        .modal-header h2 {
            margin: 0;
            color: #333;
        }

        .close-modal {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px 16px;
            cursor: pointer;
            font-size: 16px;
        }

        .close-modal:hover {
            background: #c82333;
        }

        .settings-section {
            margin-bottom: 30px;
        }

        .settings-section h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .key-selector {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }

        .switch-button-option {
            padding: 12px;
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
            font-weight: bold;
        }

        .switch-button-option:hover {
            background: #e9ecef;
            border-color: #667eea;
        }

        .switch-button-option.selected {
            background: #667eea;
            color: white;
            border-color: #5568d3;
        }

        .key-config-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .key-config-item-info {
            flex: 1;
        }

        .key-config-item-key {
            font-weight: bold;
            color: #333;
            margin-right: 10px;
        }

        .key-config-item-switch {
            color: #666;
            font-size: 14px;
        }

        .key-config-item-actions {
            display: flex;
            gap: 10px;
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 14px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-edit {
            background: #667eea;
            color: white;
        }

        .btn-edit:hover {
            background: #5568d3;
        }

        .btn-remove {
            background: #dc3545;
            color: white;
        }

        .btn-remove:hover {
            background: #c82333;
        }

        .btn-save {
            background: #28a745;
            color: white;
        }

        .btn-save:hover {
            background: #218838;
        }

        .btn-reset {
            background: #ffc107;
            color: #333;
        }

        .btn-reset:hover {
            background: #e0a800;
        }

        .btn-info {
            background: #17a2b8;
            color: white;
        }

        .btn-info:hover {
            background: #138496;
        }

        .btn-info.active {
            background: #28a745;
        }

        .btn-info.active:hover {
            background: #218838;
        }

        .mode-indicator {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-left: 8px;
            font-weight: normal;
        }

        .mode-indicator.controller {
            background: #667eea;
            color: white;
        }

        .mode-indicator.keyboard {
            background: #28a745;
            color: white;
        }

        .keyboard-key-editable {
            cursor: pointer;
            transition: all 0.2s;
        }

        .keyboard-key-editable:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.6);
        }

        .keyboard-key-selecting {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%) !important;
            border-color: #ff9800 !important;
            box-shadow: 0 0 25px rgba(255, 193, 7, 0.8) !important;
        }

        .modal-footer {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
        }

        /* レスポンシブ対応 */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            .key {
                width: 35px;
                min-width: 35px;
                max-width: 35px;
                height: 40px;
                padding: 8px 10px;
                font-size: 12px;
            }

            .key.space { width: 200px; min-width: 200px; max-width: 200px; }
            .key.tab { width: 50px; min-width: 50px; max-width: 50px; }
            .key.caps { width: 60px; min-width: 60px; max-width: 60px; }
            .key.shift { width: 70px; min-width: 70px; max-width: 70px; }
            .key.ctrl { width: 50px; min-width: 50px; max-width: 50px; }
            .key.alt { width: 50px; min-width: 50px; max-width: 50px; }
            .key.enter { width: 70px; min-width: 70px; max-width: 70px; }
            .key.backspace { width: 70px; min-width: 70px; max-width: 70px; }

            .key-switch-label {
                font-size: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>JoyDeck</h1>

        <div id="status" class="status disconnected">
            ❌ マイコン未接続
        </div>

        <!-- キーボードビジュアル -->
        <div class="keyboard-container">
            <div class="keyboard" id="keyboard">
                <!-- キーボードはJavaScriptで動的に生成 -->
            </div>
        </div>

        <div class="accordion">
            <div class="accordion-header" onclick="toggleKeyMapping()">
                <h3>📋 キーマッピング</h3>
                <span class="accordion-icon" id="keyMappingIcon">▼</span>
            </div>
            <div class="accordion-content" id="keyMappingContent">
                <div class="key-mapping" id="keyMapping">
                    <!-- キーマッピングはJavaScriptで動的に生成 -->
                </div>
            </div>
        </div>

        <div class="button-group">
            <button id="connectBtn" class="btn-primary" onclick="connectMicrocontroller()">
                マイコン接続
            </button>
            <button id="disconnectBtn" class="btn-danger" onclick="disconnectMicrocontroller()" disabled>
                切断
            </button>
            <button id="settingsBtn" class="btn-primary" onclick="openSettings()" style="background: #28a745;">
                キーマッピング設定
            </button>
            <button id="keyboardModeBtn" class="btn-primary" onclick="toggleKeyboardMode()" style="background: #17a2b8;">
                <span id="keyboardModeText">キーボードモード</span>
            </button>
            <button id="instructionsBtn" class="btn-primary" onclick="openInstructions()" style="background: #6c757d;">
                📋 使い方
            </button>
            <button id="logBtn" class="btn-primary" onclick="openLog()" style="background: #6c757d;">
                📝 ログ
            </button>
        </div>

    </div>

    <!-- 設定モーダル -->
    <div id="settingsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>⚙️ キーマッピング設定</h2>
                <button class="close-modal" onclick="closeSettings()">閉じる</button>
            </div>

            <div class="settings-section">
                <h3>📝 設定方法</h3>
                <p style="color: #666; margin-bottom: 15px;">
                    1. 下のキーボードビューから設定したいキーをクリック<br>
                    2. 右側のSwitchボタン一覧から割り当てたいボタンを選択<br>
                    3. 「保存」ボタンをクリックして設定を保存
                </p>
            </div>

            <div class="settings-section">
                <h3>⌨️ キーボード（クリックして設定）</h3>
                <div class="keyboard-container" style="margin-bottom: 20px;">
                    <div class="keyboard" id="settingsKeyboard">
                        <!-- 設定用キーボードはJavaScriptで動的に生成 -->
                    </div>
                </div>
            </div>

            <div class="settings-section">
                <h3>🎮 Switchボタン選択</h3>
                <div id="switchButtonSelector" class="key-selector">
                    <!-- SwitchボタンはJavaScriptで動的に生成 -->
                </div>
            </div>

            <div class="settings-section">
                <h3>📋 現在のマッピング一覧</h3>
                <div id="mappingList">
                    <!-- マッピング一覧はJavaScriptで動的に生成 -->
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn-small btn-reset" onclick="resetToDefault()">
                    デフォルトに戻す
                </button>
                <div>
                    <button class="btn-small btn-save" onclick="saveKeyMapping()">
                        保存
                    </button>
                    <button class="btn-small close-modal" onclick="closeSettings()" style="margin-left: 10px;">
                        キャンセル
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 使い方モーダル -->
    <div id="instructionsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📋 使い方</h2>
                <button class="close-modal" onclick="closeInstructions()">閉じる</button>
            </div>

            <div class="settings-section">
                <ul style="color: #333; margin-left: 20px; line-height: 1.8;">
                    <li>このページを開いた状態でキーボード入力をキャプチャします</li>
                    <li><strong>コントローラーモード</strong>: キーボードのキーを押すと、対応するSwitchコントローラーのボタンが送信されます</li>
                    <li><strong>キーボードモード</strong>: キーボード入力をSwitchにキーボード入力として送信します（Switchで文字入力が必要な場面で使用）</li>
                    <li>「キーボードモード」ボタンで切り替え可能です</li>
                    <li>キーボードモードでは、入力フィールド以外でキーを押すと、Switchにキーボード入力として送信されます</li>
                    <li>マイコンが接続されていることを確認してください</li>
                </ul>
            </div>

            <div class="modal-footer">
                <button class="btn-small close-modal" onclick="closeInstructions()">
                    閉じる
                </button>
            </div>
        </div>
    </div>

    <!-- ログモーダル -->
    <div id="logModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📝 ログ</h2>
                <button class="close-modal" onclick="closeLog()">閉じる</button>
            </div>

            <div class="settings-section">
                <div class="log" id="log">
                    <div class="log-entry">ログがここに表示されます...</div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn-small close-modal" onclick="closeLog()">
                    閉じる
                </button>
            </div>
        </div>
    </div>

    <script>
        // Switchボタンの定義
        const switchButtons = {
            'UP': { label: '上', icon: '⬆️', keyboardLabel: '上' },
            'DOWN': { label: '下', icon: '⬇️', keyboardLabel: '下' },
            'LEFT': { label: '左', icon: '⬅️', keyboardLabel: '左' },
            'RIGHT': { label: '右', icon: '➡️', keyboardLabel: '右' },
            'A': { label: 'Aボタン', icon: '🔴', keyboardLabel: 'A' },
            'B': { label: 'Bボタン', icon: '🔵', keyboardLabel: 'B' },
            'X': { label: 'Xボタン', icon: '🟡', keyboardLabel: 'X' },
            'Y': { label: 'Yボタン', icon: '🟢', keyboardLabel: 'Y' },
            'L1': { label: 'L1(L)ボタン', icon: 'L1', keyboardLabel: 'L1(L)' },
            'L2': { label: 'L2(ZL)ボタン', icon: 'L2', keyboardLabel: 'L2(ZL)' },
            'L3': { label: 'L3ボタン', icon: 'L3', keyboardLabel: 'L3' },
            'R1': { label: 'R1(R)ボタン', icon: 'R1', keyboardLabel: 'R1(R)' },
            'R2': { label: 'R2(ZR)ボタン', icon: 'R2', keyboardLabel: 'R2(ZR)' },
            'R3': { label: 'R3ボタン', icon: 'R3', keyboardLabel: 'R3' },
            'PLUS': { label: 'プラス', icon: '+', keyboardLabel: '+' },
            'MINUS': { label: 'マイナス', icon: '-', keyboardLabel: '-' },
            'HOME': { label: 'ホーム', icon: '🏠', keyboardLabel: '⌂' },
            'CAPTURE': { label: 'キャプチャ', icon: '📷', keyboardLabel: '●' },
            'L_STICK_CLICK': { label: '左スティック押し込み', icon: '🕹️', keyboardLabel: 'LS' },
            'R_STICK_CLICK': { label: '右スティック押し込み', icon: '🕹️', keyboardLabel: 'RS' },
        };

        // デフォルトのキーマッピング
        const defaultKeyMapping = {
            'KeyW': { switchButton: 'UP', label: '上' },
            'KeyS': { switchButton: 'DOWN', label: '下' },
            'KeyA': { switchButton: 'LEFT', label: '左' },
            'KeyD': { switchButton: 'RIGHT', label: '右' },
            'KeyJ': { switchButton: 'A', label: 'Aボタン' },
            'KeyK': { switchButton: 'B', label: 'Bボタン' },
            'KeyI': { switchButton: 'X', label: 'Xボタン' },
            'KeyL': { switchButton: 'Y', label: 'Yボタン' },
            'KeyQ': { switchButton: 'L1', label: 'L1(L)ボタン' },
            'Digit1': { switchButton: 'L2', label: 'L2(ZL)ボタン' },
            'Digit2': { switchButton: 'L3', label: 'L3ボタン' },
            'KeyE': { switchButton: 'R1', label: 'R1(R)ボタン' },
            'Digit3': { switchButton: 'R2', label: 'R2(ZR)ボタン' },
            'Digit4': { switchButton: 'R3', label: 'R3ボタン' },
            'KeyM': { switchButton: 'MINUS', label: 'マイナス' },
            'KeyN': { switchButton: 'PLUS', label: 'プラス' },
            'KeyH': { switchButton: 'HOME', label: 'ホーム' },
            'KeyG': { switchButton: 'CAPTURE', label: 'キャプチャ' },
            'Space': { switchButton: 'L_STICK_CLICK', label: '左スティック押し込み' },
            'Enter': { switchButton: 'R_STICK_CLICK', label: '右スティック押し込み' },
        };

        let isConnected = false;
        let activeKeys = new Set();
        let keyboardMode = 'controller'; // 'controller' または 'keyboard'

        // 設定の読み込み関数（先に定義）
        function loadKeyMapping() {
            const saved = localStorage.getItem('joydeck_key_mapping');
            if (saved) {
                try {
                    return JSON.parse(saved);
                } catch (e) {
                    console.error('Failed to load key mapping:', e);
                }
            }
            return JSON.parse(JSON.stringify(defaultKeyMapping));
        }

        // キーボードとSwitchコントローラーのマッピング（保存された設定を読み込む）
        let keyMapping = loadKeyMapping();

        // 設定モーダル用の変数
        let selectedKeyCode = null;
        let editingMapping = JSON.parse(JSON.stringify(keyMapping)); // 編集用のコピー

        // キーボードレイアウト定義
        const keyboardLayout = [
            [
                { code: 'Escape', label: 'Esc', class: '' },
                { code: 'F1', label: 'F1', class: '' },
                { code: 'F2', label: 'F2', class: '' },
                { code: 'F3', label: 'F3', class: '' },
                { code: 'F4', label: 'F4', class: '' },
                { code: 'F5', label: 'F5', class: '' },
                { code: 'F6', label: 'F6', class: '' },
                { code: 'F7', label: 'F7', class: '' },
                { code: 'F8', label: 'F8', class: '' },
                { code: 'F9', label: 'F9', class: '' },
                { code: 'F10', label: 'F10', class: '' },
                { code: 'F11', label: 'F11', class: '' },
                { code: 'F12', label: 'F12', class: '' },
            ],
            [
                { code: 'Backquote', label: '`', class: '' },
                { code: 'Digit1', label: '1', class: '' },
                { code: 'Digit2', label: '2', class: '' },
                { code: 'Digit3', label: '3', class: '' },
                { code: 'Digit4', label: '4', class: '' },
                { code: 'Digit5', label: '5', class: '' },
                { code: 'Digit6', label: '6', class: '' },
                { code: 'Digit7', label: '7', class: '' },
                { code: 'Digit8', label: '8', class: '' },
                { code: 'Digit9', label: '9', class: '' },
                { code: 'Digit0', label: '0', class: '' },
                { code: 'Minus', label: '-', class: '' },
                { code: 'Equal', label: '=', class: '' },
                { code: 'Backspace', label: 'Backspace', class: 'backspace' },
            ],
            [
                { code: 'Tab', label: 'Tab', class: 'tab' },
                { code: 'KeyQ', label: 'Q', class: '', switchLabel: 'L1' },
                { code: 'KeyW', label: 'W', class: '', switchLabel: '上' },
                { code: 'KeyE', label: 'E', class: '', switchLabel: 'R1' },
                { code: 'KeyR', label: 'R', class: '' },
                { code: 'KeyT', label: 'T', class: '' },
                { code: 'KeyY', label: 'Y', class: '' },
                { code: 'KeyU', label: 'U', class: '' },
                { code: 'KeyI', label: 'I', class: '', switchLabel: 'X' },
                { code: 'KeyO', label: 'O', class: '' },
                { code: 'KeyP', label: 'P', class: '' },
                { code: 'BracketLeft', label: '[', class: '' },
                { code: 'BracketRight', label: ']', class: '' },
                { code: 'Backslash', label: '\\', class: '' },
            ],
            [
                { code: 'CapsLock', label: 'Caps', class: 'caps' },
                { code: 'KeyA', label: 'A', class: '', switchLabel: '左' },
                { code: 'KeyS', label: 'S', class: '', switchLabel: '下' },
                { code: 'KeyD', label: 'D', class: '', switchLabel: '右' },
                { code: 'KeyF', label: 'F', class: '' },
                { code: 'KeyG', label: 'G', class: '', switchLabel: 'キャプチャ' },
                { code: 'KeyH', label: 'H', class: '', switchLabel: 'ホーム' },
                { code: 'KeyJ', label: 'J', class: '', switchLabel: 'A' },
                { code: 'KeyK', label: 'K', class: '', switchLabel: 'B' },
                { code: 'KeyL', label: 'L', class: '', switchLabel: 'Y' },
                { code: 'Semicolon', label: ';', class: '' },
                { code: 'Quote', label: "'", class: '' },
                { code: 'Enter', label: 'Enter', class: 'enter', switchLabel: '右スティック' },
            ],
            [
                { code: 'ShiftLeft', label: 'Shift', class: 'shift' },
                { code: 'KeyZ', label: 'Z', class: '', switchLabel: 'ZL' },
                { code: 'KeyX', label: 'X', class: '' },
                { code: 'KeyC', label: 'C', class: '', switchLabel: 'ZR' },
                { code: 'KeyV', label: 'V', class: '' },
                { code: 'KeyB', label: 'B', class: '' },
                { code: 'KeyN', label: 'N', class: '', switchLabel: 'プラス' },
                { code: 'KeyM', label: 'M', class: '', switchLabel: 'マイナス' },
                { code: 'Comma', label: ',', class: '' },
                { code: 'Period', label: '.', class: '' },
                { code: 'Slash', label: '/', class: '' },
                { code: 'ShiftRight', label: 'Shift', class: 'shift' },
            ],
            [
                { code: 'ControlLeft', label: 'Ctrl', class: 'ctrl' },
                { code: 'MetaLeft', label: 'Win', class: '' },
                { code: 'AltLeft', label: 'Alt', class: 'alt' },
                { code: 'Space', label: 'Space', class: 'space', switchLabel: '左スティック' },
                { code: 'AltRight', label: 'Alt', class: 'alt' },
                { code: 'MetaRight', label: 'Win', class: '' },
                { code: 'ContextMenu', label: 'Menu', class: '' },
                { code: 'ControlRight', label: 'Ctrl', class: 'ctrl' },
            ],
        ];

        // キーボードビジュアルを生成
        function renderKeyboard(containerId = 'keyboard', editable = false) {
            const container = document.getElementById(containerId);
            container.innerHTML = '';

            keyboardLayout.forEach((row, rowIndex) => {
                const rowElement = document.createElement('div');
                rowElement.className = 'keyboard-row';

                row.forEach(key => {
                    const keyElement = document.createElement('div');
                    keyElement.className = `key ${key.class}`;
                    keyElement.id = `${containerId}-key-${key.code}`;
                    keyElement.dataset.code = key.code;

                    // マッピングされているキーかチェック
                    const mapping = editable ? editingMapping : keyMapping;
                    const mapped = mapping[key.code];
                    
                    // キーボードモードのメインキーボードビューでは、すべてのキーをマッピング色にする
                    if (containerId === 'keyboard' && keyboardMode === 'keyboard' && !editable) {
                        keyElement.classList.add('mapped');
                    } else if (mapped) {
                        keyElement.classList.add('mapped');
                    }

                    // 編集可能な場合はクリックイベントを追加
                    if (editable) {
                        keyElement.classList.add('keyboard-key-editable');
                        keyElement.addEventListener('click', () => selectKeyForMapping(key.code));
                    }

                    // キーボードモードの時、または設定モーダル内の場合は通常のキーラベルを表示
                    // コントローラーモードでメインのキーボードビューの場合のみSwitchボタンラベルを表示
                    let displayLabel = key.label;
                    
                    if (containerId === 'keyboard' && keyboardMode === 'controller' && !editable) {
                        // コントローラーモードのメインキーボードビューでは、マッピングされている場合はSwitchボタンラベルを表示
                        if (mapped && switchButtons[mapped.switchButton]) {
                            displayLabel = switchButtons[mapped.switchButton].keyboardLabel || switchButtons[mapped.switchButton].label;
                        }
                    }

                    keyElement.innerHTML = `
                        <span class="key-label">${displayLabel}</span>
                    `;

                    rowElement.appendChild(keyElement);
                });

                container.appendChild(rowElement);
            });
        }

        // キーマッピング表示を生成
        function renderKeyMapping() {
            const container = document.getElementById('keyMapping');
            container.innerHTML = '';

            // ボタンの表示順序を定義（L/Rペアで表示）
            const buttonOrder = [
                ['UP', 'DOWN'],
                ['LEFT', 'RIGHT'],
                ['A', 'B'],
                ['X', 'Y'],
                ['L1', 'R1'],
                ['L2', 'R2'],
                ['L3', 'R3'],
                ['PLUS', 'MINUS'],
                ['HOME', 'CAPTURE'],
                ['L_STICK_CLICK', 'R_STICK_CLICK'],
            ];

            // 順序に従って表示
            const displayedButtons = new Set();
            buttonOrder.forEach(([leftBtn, rightBtn]) => {
                // 左側のボタン
                const leftMapping = Object.entries(keyMapping).find(([key, value]) => value.switchButton === leftBtn);
                if (leftMapping) {
                    const [key, value] = leftMapping;
                    displayedButtons.add(key);
                    const keyItem = document.createElement('div');
                    keyItem.className = 'key-item';
                    keyItem.id = `key-${key}`;
                    keyItem.innerHTML = `
                        <span class="key-label">${value.label}</span>
                        <span class="key-value">${key}</span>
                    `;
                    container.appendChild(keyItem);
                }

                // 右側のボタン
                const rightMapping = Object.entries(keyMapping).find(([key, value]) => value.switchButton === rightBtn);
                if (rightMapping) {
                    const [key, value] = rightMapping;
                    displayedButtons.add(key);
                    const keyItem = document.createElement('div');
                    keyItem.className = 'key-item';
                    keyItem.id = `key-${key}`;
                    keyItem.innerHTML = `
                        <span class="key-label">${value.label}</span>
                        <span class="key-value">${key}</span>
                    `;
                    container.appendChild(keyItem);
                }
            });

            // 順序に含まれていないボタンも表示
            Object.entries(keyMapping).forEach(([key, value]) => {
                if (!displayedButtons.has(key)) {
                    const keyItem = document.createElement('div');
                    keyItem.className = 'key-item';
                    keyItem.id = `key-${key}`;
                    keyItem.innerHTML = `
                        <span class="key-label">${value.label}</span>
                        <span class="key-value">${key}</span>
                    `;
                    container.appendChild(keyItem);
                }
            });
        }

        // ログ追加
        function addLog(message, type = '') {
            const log = document.getElementById('log');
            const entry = document.createElement('div');
            entry.className = `log-entry ${type}`;
            entry.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
            log.insertBefore(entry, log.firstChild);
            
            // ログが多すぎる場合は削除
            while (log.children.length > 50) {
                log.removeChild(log.lastChild);
            }
        }

        // マイコン接続
        async function connectMicrocontroller() {
            try {
                const response = await fetch('/api/microcontroller/connect', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    isConnected = true;
                    updateStatus(true);
                    document.getElementById('connectBtn').disabled = true;
                    document.getElementById('disconnectBtn').disabled = false;
                    addLog('マイコンに接続しました', 'success');
                } else {
                    addLog('接続に失敗しました: ' + data.message, 'error');
                }
            } catch (error) {
                addLog('接続エラー: ' + error.message, 'error');
            }
        }

        // マイコン切断
        async function disconnectMicrocontroller() {
            try {
                const response = await fetch('/api/microcontroller/disconnect', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();
                
                isConnected = false;
                updateStatus(false);
                document.getElementById('connectBtn').disabled = false;
                document.getElementById('disconnectBtn').disabled = true;
                addLog('マイコンから切断しました', 'success');
            } catch (error) {
                addLog('切断エラー: ' + error.message, 'error');
            }
        }

        // ステータス更新
        function updateStatus(connected) {
            const status = document.getElementById('status');
            if (connected) {
                status.className = 'status connected';
                status.textContent = '✅ マイコン接続中';
            } else {
                status.className = 'status disconnected';
                status.textContent = '❌ マイコン未接続';
            }
        }

        // キー送信
        async function sendKey(keyCode, pressed) {
            // キーボードモードの場合は、Switchコマンドを送信しない
            if (keyboardMode === 'keyboard') return;
            
            if (!isConnected) return;

            const mapping = keyMapping[keyCode];
            if (!mapping) return;

            try {
                const response = await fetch('/api/switch/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        button: mapping.switchButton,
                        pressed: pressed
                    })
                });

                const data = await response.json();
                if (!data.success) {
                    addLog(`送信エラー: ${mapping.label}`, 'error');
                }
            } catch (error) {
                addLog(`送信エラー: ${error.message}`, 'error');
            }
        }

        // キーボードモードの切り替え
        function toggleKeyboardMode() {
            keyboardMode = keyboardMode === 'controller' ? 'keyboard' : 'controller';
            updateKeyboardModeUI();
            
            // キーボードビューを再描画（モードに応じて表示を変更）
            renderKeyboard();
            
            const modeText = keyboardMode === 'controller' ? 'コントローラーモード' : 'キーボードモード';
            addLog(`${modeText}に切り替えました`, 'success');
            
            // 設定を保存
            localStorage.setItem('joydeck_keyboard_mode', keyboardMode);
        }

        function updateKeyboardModeUI() {
            const btn = document.getElementById('keyboardModeBtn');
            const text = document.getElementById('keyboardModeText');
            
            if (keyboardMode === 'controller') {
                btn.style.background = '#667eea';
                text.textContent = 'コントローラーモード';
                btn.classList.add('active');
            } else {
                btn.style.background = '#28a745';
                text.textContent = 'キーボードモード';
                btn.classList.remove('active');
            }
        }

        // キーボードモードの読み込み
        function loadKeyboardMode() {
            const saved = localStorage.getItem('joydeck_keyboard_mode');
            if (saved === 'keyboard' || saved === 'controller') {
                keyboardMode = saved;
            }
            updateKeyboardModeUI();
        }

        // キーボード入力送信（キーボードモード用）
        async function sendKeyboardInput(char, key = null) {
            if (!isConnected) return;

            try {
                const body = {};
                if (char) {
                    body.char = char;
                }
                if (key) {
                    body.key = key;
                }

                const response = await fetch('/api/switch/keyboard', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(body)
                });

                const data = await response.json();
                if (!data.success) {
                    addLog(`キーボード入力エラー: ${data.message}`, 'error');
                }
            } catch (error) {
                addLog(`キーボード入力エラー: ${error.message}`, 'error');
            }
        }

        // キーボードコードから特殊キー名に変換
        function getSpecialKeyName(keyCode) {
            const keyMap = {
                'Enter': 'ENTER',
                'Backspace': 'BACKSPACE',
                'Tab': 'TAB',
                'Escape': 'ESC',
                'Space': 'SPACE',
                'Delete': 'DELETE',
                'Home': 'HOME',
                'End': 'END',
                'PageUp': 'PAGEUP',
                'PageDown': 'PAGEDOWN',
                'ArrowUp': 'ARROW_UP',
                'ArrowDown': 'ARROW_DOWN',
                'ArrowLeft': 'ARROW_LEFT',
                'ArrowRight': 'ARROW_RIGHT',
            };
            return keyMap[keyCode] || null;
        }

        // キーボードイベント
        document.addEventListener('keydown', (e) => {
            // 設定モーダルが開いている場合は通常のキーボード入力を許可
            const modal = document.getElementById('settingsModal');
            if (modal && modal.classList.contains('active')) {
                return;
            }

            if (activeKeys.has(e.code)) return;
            activeKeys.add(e.code);

            // キーボードモードの場合
            if (keyboardMode === 'keyboard') {
                // テキスト入力フィールドにフォーカスがある場合は通常入力として扱う
                const activeElement = document.activeElement;
                const isInputField = activeElement && (
                    activeElement.tagName === 'INPUT' ||
                    activeElement.tagName === 'TEXTAREA' ||
                    activeElement.isContentEditable
                );
                
                // 入力フィールド以外の場合のみ、Switchにキーボード入力を送信
                if (!isInputField && isConnected) {
                    // 特殊キーの場合
                    const specialKey = getSpecialKeyName(e.code);
                    if (specialKey) {
                        sendKeyboardInput(null, specialKey);
                    } else if (e.key && e.key.length === 1) {
                        // 通常の文字の場合
                        sendKeyboardInput(e.key);
                    }
                }
                
                // キーボードビジュアルのハイライト（キーボードモードでも実行）
                const keyboardKey = document.getElementById(`keyboard-key-${e.code}`);
                if (keyboardKey) {
                    keyboardKey.classList.add('pressed');
                }
                
                return; // キーボードモードでは、コントローラーコマンドは送信しない
            }

            // コントローラーモードの場合
            // マッピングされているキーの場合のみ処理
            const mapping = keyMapping[e.code];
            if (mapping) {
                // テキスト入力フィールドにフォーカスがある場合は通常入力として扱う
                const activeElement = document.activeElement;
                const isInputField = activeElement && (
                    activeElement.tagName === 'INPUT' ||
                    activeElement.tagName === 'TEXTAREA' ||
                    activeElement.isContentEditable
                );
                
                if (!isInputField) {
                    // 通常のキーボード入力を抑制（ただし、完全にはブロックしない）
                    // ゲームプレイ中に誤って文字が入力されるのを防ぐ
                    e.preventDefault();
                }
            }

            // キーマッピングリストのハイライト
            const keyItem = document.getElementById(`key-${e.code}`);
            if (keyItem) {
                keyItem.classList.add('active');
            }

            // キーボードビジュアルのハイライト
            const keyboardKey = document.getElementById(`keyboard-key-${e.code}`);
            if (keyboardKey) {
                keyboardKey.classList.add('pressed');
            }

            sendKey(e.code, true);
        });

        document.addEventListener('keyup', (e) => {
            activeKeys.delete(e.code);

            // キーマッピングリストのハイライト解除
            const keyItem = document.getElementById(`key-${e.code}`);
            if (keyItem) {
                keyItem.classList.remove('active');
            }

            // キーボードビジュアルのハイライト解除（キーボードモードでも実行）
            const keyboardKey = document.getElementById(`keyboard-key-${e.code}`);
            if (keyboardKey) {
                keyboardKey.classList.remove('pressed');
            }

            // コントローラーモードの場合のみsendKeyを実行
            if (keyboardMode === 'controller') {
                sendKey(e.code, false);
            }
        });

        // 設定の保存と読み込み
        function saveKeyMapping() {
            localStorage.setItem('joydeck_key_mapping', JSON.stringify(editingMapping));
            keyMapping = JSON.parse(JSON.stringify(editingMapping));
            renderKeyboard();
            renderKeyMapping();
            renderMappingList();
            addLog('キーマッピングを保存しました', 'success');
            closeSettings();
        }

        function resetToDefault() {
            if (confirm('デフォルト設定に戻しますか？現在の設定は失われます。')) {
                editingMapping = JSON.parse(JSON.stringify(defaultKeyMapping));
                renderKeyboard('settingsKeyboard', true);
                renderMappingList();
                addLog('デフォルト設定にリセットしました', 'success');
            }
        }

        // 設定モーダル関連
        function openSettings() {
            editingMapping = JSON.parse(JSON.stringify(keyMapping));
            selectedKeyCode = null;
            document.getElementById('settingsModal').classList.add('active');
            renderKeyboard('settingsKeyboard', true);
            renderSwitchButtonSelector();
            renderMappingList();
        }

        function closeSettings() {
            document.getElementById('settingsModal').classList.remove('active');
            selectedKeyCode = null;
        }

        // 使い方モーダルの開閉
        function openInstructions() {
            document.getElementById('instructionsModal').classList.add('active');
        }

        function closeInstructions() {
            document.getElementById('instructionsModal').classList.remove('active');
        }

        // ログモーダルの開閉
        function openLog() {
            document.getElementById('logModal').classList.add('active');
        }

        function closeLog() {
            document.getElementById('logModal').classList.remove('active');
        }

        function selectKeyForMapping(keyCode) {
            selectedKeyCode = keyCode;
            
            // 選択中のキーをハイライト
            document.querySelectorAll('#settingsKeyboard .key').forEach(key => {
                key.classList.remove('keyboard-key-selecting');
            });
            const selectedKey = document.getElementById(`settingsKeyboard-key-${keyCode}`);
            if (selectedKey) {
                selectedKey.classList.add('keyboard-key-selecting');
            }

            // Switchボタン選択をリセット
            document.querySelectorAll('.switch-button-option').forEach(btn => {
                btn.classList.remove('selected');
            });

            // 既にマッピングされている場合は選択状態にする
            if (editingMapping[keyCode]) {
                const switchBtn = document.querySelector(`[data-switch-button="${editingMapping[keyCode].switchButton}"]`);
                if (switchBtn) {
                    switchBtn.classList.add('selected');
                }
            }
        }

        function selectSwitchButton(switchButton) {
            if (!selectedKeyCode) {
                alert('まずキーボードのキーを選択してください');
                return;
            }

            // 既に同じSwitchボタンにマッピングされているキーを削除
            Object.keys(editingMapping).forEach(key => {
                if (editingMapping[key].switchButton === switchButton && key !== selectedKeyCode) {
                    delete editingMapping[key];
                }
            });

            // マッピングを設定
            editingMapping[selectedKeyCode] = {
                switchButton: switchButton,
                label: switchButtons[switchButton].label
            };

            // UIを更新
            renderKeyboard('settingsKeyboard', true);
            renderMappingList();
            
            // Switchボタン選択を更新
            document.querySelectorAll('.switch-button-option').forEach(btn => {
                btn.classList.remove('selected');
            });
            const selectedBtn = document.querySelector(`[data-switch-button="${switchButton}"]`);
            if (selectedBtn) {
                selectedBtn.classList.add('selected');
            }
        }

        function removeKeyMapping(keyCode) {
            if (confirm('このマッピングを削除しますか？')) {
                delete editingMapping[keyCode];
                renderKeyboard('settingsKeyboard', true);
                renderMappingList();
            }
        }

        function renderSwitchButtonSelector() {
            const container = document.getElementById('switchButtonSelector');
            container.innerHTML = '';

            Object.entries(switchButtons).forEach(([code, info]) => {
                const button = document.createElement('div');
                button.className = 'switch-button-option';
                button.dataset.switchButton = code;
                button.innerHTML = `${info.icon} ${info.label}`;
                button.addEventListener('click', () => selectSwitchButton(code));
                container.appendChild(button);
            });
        }

        function renderMappingList() {
            const container = document.getElementById('mappingList');
            container.innerHTML = '';

            const mappings = Object.entries(editingMapping);
            if (mappings.length === 0) {
                container.innerHTML = '<p style="color: #666; text-align: center;">マッピングがありません</p>';
                return;
            }

            mappings.forEach(([keyCode, mapping]) => {
                const item = document.createElement('div');
                item.className = 'key-config-item';
                
                const keyInfo = keyboardLayout.flat().find(k => k.code === keyCode);
                const keyLabel = keyInfo ? keyInfo.label : keyCode;
                const switchInfo = switchButtons[mapping.switchButton];

                item.innerHTML = `
                    <div class="key-config-item-info">
                        <span class="key-config-item-key">${keyLabel}</span>
                        <span class="key-config-item-switch">→ ${switchInfo.icon} ${switchInfo.label}</span>
                    </div>
                    <div class="key-config-item-actions">
                        <button class="btn-small btn-edit" onclick="selectKeyForMapping('${keyCode}'); document.querySelector('[data-switch-button=\"${mapping.switchButton}\"]').scrollIntoView({behavior: 'smooth', block: 'center'});">
                            編集
                        </button>
                        <button class="btn-small btn-remove" onclick="removeKeyMapping('${keyCode}')">
                            削除
                        </button>
                    </div>
                `;
                container.appendChild(item);
            });
        }

        // モーダル外クリックで閉じる
        window.onclick = function(event) {
            const settingsModal = document.getElementById('settingsModal');
            if (event.target === settingsModal) {
                closeSettings();
            }
            const instructionsModal = document.getElementById('instructionsModal');
            if (event.target === instructionsModal) {
                closeInstructions();
            }
            const logModal = document.getElementById('logModal');
            if (event.target === logModal) {
                closeLog();
            }
        }

        // キーマッピングアコーディオンの開閉
        function toggleKeyMapping() {
            const content = document.getElementById('keyMappingContent');
            const icon = document.getElementById('keyMappingIcon');
            
            if (content.classList.contains('open')) {
                content.classList.remove('open');
                icon.classList.remove('open');
            } else {
                content.classList.add('open');
                icon.classList.add('open');
            }
        }

        // ページ読み込み時にキーボードとキーマッピングを表示
        loadKeyboardMode();
        renderKeyboard();
        renderKeyMapping();
        // アコーディオンは初期状態で閉じた状態にする
        addLog('JoyDeckが起動しました');
    </script>
</body>
</html>

