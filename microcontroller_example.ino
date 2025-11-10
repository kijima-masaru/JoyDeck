/*
 * JoyDeck - Arduino側の実装例
 * 
 * このスケッチは、PCからのコマンドを受信して
 * Nintendo Switchに送信するための基本的な実装例です。
 * 
 * 注意: 実際のSwitch操作には、専用のライブラリ（例: Switch-Fightstick）
 * が必要です。この例は基本的な構造を示しています。
 */

// WiFi接続用（ESP32/ESP8266の場合）
#include <WiFi.h>
#include <WiFiClient.h>

// シリアル接続用（Arduino Uno等の場合）
// #define USE_SERIAL

// WiFi設定
const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";
const int serverPort = 8888;

WiFiServer server(serverPort);
WiFiClient client;

// Switchコントローラーのボタン定義
enum SwitchButton {
  BTN_A, BTN_B, BTN_X, BTN_Y,
  BTN_L, BTN_R, BTN_ZL, BTN_ZR,
  BTN_PLUS, BTN_MINUS, BTN_HOME, BTN_CAPTURE,
  BTN_UP, BTN_DOWN, BTN_LEFT, BTN_RIGHT,
  BTN_L_STICK_CLICK, BTN_R_STICK_CLICK
};

// 現在のボタン状態
bool buttonStates[18] = {false};

void setup() {
  Serial.begin(115200);
  
  #ifdef USE_SERIAL
    // シリアル接続のみ使用
    Serial.println("JoyDeck Serial Mode");
  #else
    // WiFi接続
    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) {
      delay(500);
      Serial.print(".");
    }
    Serial.println("");
    Serial.println("WiFi connected");
    Serial.println("IP address: ");
    Serial.println(WiFi.localIP());
    
    server.begin();
    Serial.println("Server started");
  #endif
}

void loop() {
  #ifdef USE_SERIAL
    // シリアル接続からコマンドを受信
    if (Serial.available()) {
      String command = Serial.readStringUntil('\n');
      command.trim();
      processCommand(command);
    }
  #else
    // WiFi接続からコマンドを受信
    if (!client || !client.connected()) {
      client = server.available();
      if (client) {
        Serial.println("Client connected");
      }
    } else {
      if (client.available()) {
        String command = client.readStringUntil('\n');
        command.trim();
        processCommand(command);
      }
    }
  #endif
}

void processCommand(String command) {
  Serial.println("Received: " + command);
  
  if (command.startsWith("BUTTON:")) {
    processButtonCommand(command);
  } else if (command.startsWith("DPAD:")) {
    processDpadCommand(command);
  } else if (command.startsWith("STICK:")) {
    processStickCommand(command);
  }
}

void processButtonCommand(String cmd) {
  // フォーマット: "BUTTON:A:PRESS" または "BUTTON:A:RELEASE"
  int firstColon = cmd.indexOf(':');
  int secondColon = cmd.indexOf(':', firstColon + 1);
  
  if (firstColon == -1 || secondColon == -1) {
    return;
  }
  
  String button = cmd.substring(firstColon + 1, secondColon);
  String action = cmd.substring(secondColon + 1);
  
  SwitchButton btn = parseButton(button);
  if (btn == -1) {
    return;
  }
  
  bool pressed = (action == "PRESS");
  buttonStates[btn] = pressed;
  
  // 実際のSwitchコントローラーライブラリを使用してボタンを送信
  sendButtonToSwitch(btn, pressed);
}

void processDpadCommand(String cmd) {
  // フォーマット: "DPAD:UP:PRESS" または "DPAD:UP:RELEASE"
  int firstColon = cmd.indexOf(':');
  int secondColon = cmd.indexOf(':', firstColon + 1);
  
  if (firstColon == -1 || secondColon == -1) {
    return;
  }
  
  String direction = cmd.substring(firstColon + 1, secondColon);
  String action = cmd.substring(secondColon + 1);
  
  SwitchButton btn = parseDpad(direction);
  if (btn == -1) {
    return;
  }
  
  bool pressed = (action == "PRESS");
  buttonStates[btn] = pressed;
  
  sendButtonToSwitch(btn, pressed);
}

void processStickCommand(String cmd) {
  // フォーマット: "STICK:L:CLICK:PRESS" または "STICK:L:1000:-5000"
  int firstColon = cmd.indexOf(':');
  int secondColon = cmd.indexOf(':', firstColon + 1);
  int thirdColon = cmd.indexOf(':', secondColon + 1);
  
  if (firstColon == -1 || secondColon == -1) {
    return;
  }
  
  String stick = cmd.substring(firstColon + 1, secondColon);
  
  if (thirdColon != -1) {
    // クリックコマンド: "STICK:L:CLICK:PRESS"
    String click = cmd.substring(secondColon + 1, thirdColon);
    String action = cmd.substring(thirdColon + 1);
    
    if (click == "CLICK") {
      SwitchButton btn = (stick == "L") ? BTN_L_STICK_CLICK : BTN_R_STICK_CLICK;
      bool pressed = (action == "PRESS");
      buttonStates[btn] = pressed;
      sendButtonToSwitch(btn, pressed);
    }
  } else {
    // スティック位置: "STICK:L:1000:-5000"
    String xStr = cmd.substring(secondColon + 1);
    int comma = xStr.indexOf(':');
    if (comma != -1) {
      int x = xStr.substring(0, comma).toInt();
      int y = xStr.substring(comma + 1).toInt();
      sendStickPosition(stick == "L", x, y);
    }
  }
}

SwitchButton parseButton(String button) {
  if (button == "A") return BTN_A;
  if (button == "B") return BTN_B;
  if (button == "X") return BTN_X;
  if (button == "Y") return BTN_Y;
  if (button == "L") return BTN_L;
  if (button == "R") return BTN_R;
  if (button == "ZL") return BTN_ZL;
  if (button == "ZR") return BTN_ZR;
  if (button == "PLUS") return BTN_PLUS;
  if (button == "MINUS") return BTN_MINUS;
  if (button == "HOME") return BTN_HOME;
  if (button == "CAPTURE") return BTN_CAPTURE;
  return (SwitchButton)-1;
}

SwitchButton parseDpad(String direction) {
  if (direction == "UP") return BTN_UP;
  if (direction == "DOWN") return BTN_DOWN;
  if (direction == "LEFT") return BTN_LEFT;
  if (direction == "RIGHT") return BTN_RIGHT;
  return (SwitchButton)-1;
}

// 実際のSwitchコントローラーライブラリを使用してボタンを送信
// この関数は、使用するライブラリに応じて実装を変更する必要があります
void sendButtonToSwitch(SwitchButton button, bool pressed) {
  // TODO: 実際のSwitchコントローラーライブラリの実装
  // 例: Switch-Fightstickライブラリを使用する場合
  // controller.setButton(button, pressed);
  
  Serial.print("Button: ");
  Serial.print(button);
  Serial.print(" - ");
  Serial.println(pressed ? "PRESS" : "RELEASE");
}

// スティックの位置を送信
void sendStickPosition(bool leftStick, int x, int y) {
  // TODO: 実際のSwitchコントローラーライブラリの実装
  // 例: controller.setStick(leftStick, x, y);
  
  Serial.print("Stick: ");
  Serial.print(leftStick ? "L" : "R");
  Serial.print(" - X: ");
  Serial.print(x);
  Serial.print(", Y: ");
  Serial.println(y);
}

