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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }

        .subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
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

        .key-mapping {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 30px;
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

        .instructions {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .instructions h3 {
            color: #856404;
            margin-bottom: 10px;
        }

        .instructions ul {
            color: #856404;
            margin-left: 20px;
        }

        .instructions li {
            margin-bottom: 5px;
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
            max-height: 200px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            margin-top: 20px;
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

        .keyboard-title {
            color: white;
            text-align: center;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: bold;
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
            min-width: 50px;
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
        .key.tab { min-width: 70px; }
        .key.caps { min-width: 85px; }
        .key.shift { min-width: 100px; }
        .key.ctrl { min-width: 70px; }
        .key.alt { min-width: 70px; }
        .key.space { min-width: 300px; }
        .key.enter { min-width: 100px; }
        .key.backspace { min-width: 100px; }

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
                min-width: 35px;
                height: 40px;
                padding: 8px 10px;
                font-size: 12px;
            }

            .key.space { min-width: 200px; }
            .key.tab { min-width: 50px; }
            .key.caps { min-width: 60px; }
            .key.shift { min-width: 70px; }
            .key.ctrl { min-width: 50px; }
            .key.alt { min-width: 50px; }
            .key.enter { min-width: 70px; }
            .key.backspace { min-width: 70px; }

            .key-switch-label {
                font-size: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎮 JoyDeck</h1>
        <p class="subtitle">PCキーボードでNintendo Switchを操作</p>

        <div id="status" class="status disconnected">
            ❌ マイコン未接続
        </div>

        <div class="instructions">
            <h3>📋 使い方</h3>
            <ul>
                <li>このページを開いた状態でキーボード入力をキャプチャします</li>
                <li>キーボードのキーを押すと、対応するSwitchコントローラーのボタンが送信されます</li>
                <li>マイコンが接続されていることを確認してください</li>
            </ul>
        </div>

        <!-- キーボードビジュアル -->
        <div class="keyboard-container">
            <div class="keyboard-title">⌨️ キーボードビュー</div>
            <div class="keyboard" id="keyboard">
                <!-- キーボードはJavaScriptで動的に生成 -->
            </div>
        </div>

        <div class="key-mapping" id="keyMapping">
            <!-- キーマッピングはJavaScriptで動的に生成 -->
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
        </div>

        <div class="log" id="log">
            <div class="log-entry">ログがここに表示されます...</div>
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

    <script>
        // Switchボタンの定義
        const switchButtons = {
            'UP': { label: '上', icon: '⬆️' },
            'DOWN': { label: '下', icon: '⬇️' },
            'LEFT': { label: '左', icon: '⬅️' },
            'RIGHT': { label: '右', icon: '➡️' },
            'A': { label: 'Aボタン', icon: '🔴' },
            'B': { label: 'Bボタン', icon: '🔵' },
            'X': { label: 'Xボタン', icon: '🟡' },
            'Y': { label: 'Yボタン', icon: '🟢' },
            'L': { label: 'Lボタン', icon: 'L' },
            'R': { label: 'Rボタン', icon: 'R' },
            'ZL': { label: 'ZLボタン', icon: 'ZL' },
            'ZR': { label: 'ZRボタン', icon: 'ZR' },
            'PLUS': { label: 'プラス', icon: '+' },
            'MINUS': { label: 'マイナス', icon: '-' },
            'HOME': { label: 'ホーム', icon: '🏠' },
            'CAPTURE': { label: 'キャプチャ', icon: '📷' },
            'L_STICK_CLICK': { label: '左スティック押し込み', icon: '🕹️' },
            'R_STICK_CLICK': { label: '右スティック押し込み', icon: '🕹️' },
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
            'KeyQ': { switchButton: 'L', label: 'Lボタン' },
            'KeyE': { switchButton: 'R', label: 'Rボタン' },
            'KeyZ': { switchButton: 'ZL', label: 'ZLボタン' },
            'KeyC': { switchButton: 'ZR', label: 'ZRボタン' },
            'KeyM': { switchButton: 'MINUS', label: 'マイナス' },
            'KeyN': { switchButton: 'PLUS', label: 'プラス' },
            'KeyH': { switchButton: 'HOME', label: 'ホーム' },
            'KeyG': { switchButton: 'CAPTURE', label: 'キャプチャ' },
            'Space': { switchButton: 'L_STICK_CLICK', label: '左スティック押し込み' },
            'Enter': { switchButton: 'R_STICK_CLICK', label: '右スティック押し込み' },
        };

        let isConnected = false;
        let activeKeys = new Set();

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
                { code: 'KeyQ', label: 'Q', class: '', switchLabel: 'L' },
                { code: 'KeyW', label: 'W', class: '', switchLabel: '上' },
                { code: 'KeyE', label: 'E', class: '', switchLabel: 'R' },
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
                    if (mapped) {
                        keyElement.classList.add('mapped');
                    }

                    // 編集可能な場合はクリックイベントを追加
                    if (editable) {
                        keyElement.classList.add('keyboard-key-editable');
                        keyElement.addEventListener('click', () => selectKeyForMapping(key.code));
                    }

                    // Switchボタンラベルを表示
                    let switchLabel = '';
                    if (mapped && switchButtons[mapped.switchButton]) {
                        switchLabel = switchButtons[mapped.switchButton].label;
                    }

                    keyElement.innerHTML = `
                        <span class="key-label">${key.label}</span>
                        ${switchLabel ? `<span class="key-switch-label">${switchLabel}</span>` : ''}
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

            Object.entries(keyMapping).forEach(([key, value]) => {
                const keyItem = document.createElement('div');
                keyItem.className = 'key-item';
                keyItem.id = `key-${key}`;
                keyItem.innerHTML = `
                    <span class="key-label">${value.label}</span>
                    <span class="key-value">${key}</span>
                `;
                container.appendChild(keyItem);
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

        // キーボードイベント
        document.addEventListener('keydown', (e) => {
            if (activeKeys.has(e.code)) return;
            activeKeys.add(e.code);

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

            // キーボードビジュアルのハイライト解除
            const keyboardKey = document.getElementById(`keyboard-key-${e.code}`);
            if (keyboardKey) {
                keyboardKey.classList.remove('pressed');
            }

            sendKey(e.code, false);
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
            const modal = document.getElementById('settingsModal');
            if (event.target === modal) {
                closeSettings();
            }
        }

        // ページ読み込み時にキーボードとキーマッピングを表示
        renderKeyboard();
        renderKeyMapping();
        addLog('JoyDeckが起動しました');
    </script>
</body>
</html>

