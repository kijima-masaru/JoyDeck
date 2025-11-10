# 🎮 JoyDeck

PCのキーボード入力をマイコン経由でNintendo Switchに送信するアプリケーション

## 📋 概要

JoyDeckは、PCに接続されたキーボードの入力をキャプチャし、マイコン（Arduino等）を経由してNintendo Switchを操作できるようにするアプリケーションです。

## 🏗️ アーキテクチャ

```
PCキーボード → Webアプリ → Laravel API → マイコン → Nintendo Switch
```

## 🚀 セットアップ

### 必要な環境

- PHP 8.0以上
- Composer
- Node.js & npm
- Docker & Docker Compose（推奨）

### インストール手順

1. リポジトリをクローン
```bash
git clone <repository-url>
cd JoyDeck
```

2. Docker Composeで起動
```bash
docker-compose up -d
```

3. 依存関係のインストール
```bash
cd src
composer install
npm install
```

4. 環境変数の設定
```bash
cp .env.example .env
php artisan key:generate
```

5. `.env`ファイルにマイコン接続設定を追加
```env
MICROCONTROLLER_CONNECTION_TYPE=tcp
MICROCONTROLLER_TCP_HOST=127.0.0.1
MICROCONTROLLER_TCP_PORT=8888
```

6. アセットのビルド
```bash
npm run dev
```

## 🎯 使い方

1. マイコンをPCに接続（USBまたはネットワーク経由）
2. ブラウザで `http://localhost/controller` にアクセス
3. 「マイコン接続」ボタンをクリック
4. キーボードで操作開始

### キーボードマッピング

| キーボード | Switchボタン |
|-----------|-------------|
| W | 上 |
| S | 下 |
| A | 左 |
| D | 右 |
| J | Aボタン |
| K | Bボタン |
| I | Xボタン |
| L | Yボタン |
| Q | Lボタン |
| E | Rボタン |
| Z | ZLボタン |
| C | ZRボタン |
| M | マイナス |
| N | プラス |
| H | ホーム |
| G | キャプチャ |
| Space | 左スティック押し込み |
| Enter | 右スティック押し込み |

## 🔧 マイコン側の実装

マイコン（Arduino等）側では、以下のようなコマンドを受信してSwitchに送信する必要があります。

### コマンドフォーマット

- ボタン押下: `BUTTON:A:PRESS`
- ボタン解放: `BUTTON:A:RELEASE`
- 方向キー: `DPAD:UP:PRESS` / `DPAD:UP:RELEASE`
- スティッククリック: `STICK:L:CLICK:PRESS` / `STICK:L:CLICK:RELEASE`

### Arduino実装例

```cpp
#include <Keyboard.h>

void setup() {
  Serial.begin(115200);
  Keyboard.begin();
}

void loop() {
  if (Serial.available()) {
    String command = Serial.readStringUntil('\n');
    command.trim();
    
    // コマンドをパースして処理
    if (command.startsWith("BUTTON:")) {
      // ボタンコマンドの処理
      processButtonCommand(command);
    } else if (command.startsWith("DPAD:")) {
      // 方向キーコマンドの処理
      processDpadCommand(command);
    }
  }
}

void processButtonCommand(String cmd) {
  // コマンド例: "BUTTON:A:PRESS"
  int firstColon = cmd.indexOf(':');
  int secondColon = cmd.indexOf(':', firstColon + 1);
  
  String button = cmd.substring(firstColon + 1, secondColon);
  String action = cmd.substring(secondColon + 1);
  
  // ボタンマッピング（Keyboardライブラリのキーコードを使用）
  char key = mapButtonToKey(button);
  
  if (action == "PRESS") {
    Keyboard.press(key);
  } else if (action == "RELEASE") {
    Keyboard.release(key);
  }
}

char mapButtonToKey(String button) {
  // Switchのボタンからキーボードキーへのマッピング
  // 実際の実装では、SwitchコントローラーのHIDプロトコルを使用
  // ここでは例としてキーボードキーを返す
  return 'a'; // 実際の実装が必要
}
```

## 📡 接続タイプ

### TCP接続（推奨）

マイコンがWi-Fi経由で接続可能な場合、TCP接続を使用できます。

```env
MICROCONTROLLER_CONNECTION_TYPE=tcp
MICROCONTROLLER_TCP_HOST=192.168.1.100
MICROCONTROLLER_TCP_PORT=8888
```

### シリアル接続

USBシリアル経由で接続する場合。

```env
MICROCONTROLLER_CONNECTION_TYPE=serial
MICROCONTROLLER_SERIAL_PORT=COM3
MICROCONTROLLER_BAUD_RATE=115200
```

## 🛠️ 開発

### コントローラーの追加

新しいコントローラーボタンを追加するには、`SwitchCommandService.php`の`$validButtons`配列に追加します。

### キーマッピングの変更

`controller.blade.php`の`keyMapping`オブジェクトを編集して、キーボードとSwitchボタンのマッピングを変更できます。

## 📝 ライセンス

MIT License

## 🤝 貢献

プルリクエストを歓迎します！

