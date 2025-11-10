/*
 * JoyDeck - Arduino側の実装例
 * 
 * このスケッチは、PCからのシリアル通信でコマンドを受信して
 * Nintendo Switchに送信するための基本的な実装例です。
 * 
 * 注意: 実際のSwitch操作には、専用のライブラリ（例: Switch-Fightstick）
 * が必要です。この例は基本的な構造を示しています。
 * 
 * この実装は有線接続（シリアル通信）のみをサポートします。
 */

// Switchコントローラーのボタン定義
enum SwitchButton {
  BTN_A, BTN_B, BTN_X, BTN_Y,
  BTN_L1, BTN_L2, BTN_L3, BTN_R1, BTN_R2, BTN_R3,
  BTN_ZL, BTN_ZR,
  BTN_PLUS, BTN_MINUS, BTN_HOME, BTN_CAPTURE,
  BTN_UP, BTN_DOWN, BTN_LEFT, BTN_RIGHT,
  BTN_L_STICK_CLICK, BTN_R_STICK_CLICK
};

// 現在のボタン状態
bool buttonStates[22] = {false}; // ボタン数が増えたので配列サイズを更新

void setup() {
  Serial.begin(115200);
  Serial.println("JoyDeck Serial Mode - Ready for commands");
}

void loop() {
  // シリアル接続からコマンドを受信
  if (Serial.available()) {
    String command = Serial.readStringUntil('\n');
    command.trim();
    if (command.length() > 0) {
      processCommand(command);
    }
  }
}

void processCommand(String command) {
  Serial.println("Received: " + command);
  
  if (command.startsWith("BUTTON:")) {
    processButtonCommand(command);
  } else if (command.startsWith("DPAD:")) {
    processDpadCommand(command);
  } else if (command.startsWith("STICK:")) {
    processStickCommand(command);
  } else if (command.startsWith("KEYBOARD:")) {
    processKeyboardCommand(command);
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
  // L1→L, R1→R, L2→ZL, R2→ZRとして扱う
  if (button == "L" || button == "L1") return BTN_L1;  // L1 = L
  if (button == "ZL" || button == "L2") return BTN_L2; // L2 = ZL
  if (button == "L3") return BTN_L3;
  if (button == "R" || button == "R1") return BTN_R1;   // R1 = R
  if (button == "ZR" || button == "R2") return BTN_R2; // R2 = ZR
  if (button == "R3") return BTN_R3;
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

// キーボード入力コマンドを処理
void processKeyboardCommand(String cmd) {
  // フォーマット: "KEYBOARD:CHAR:a" または "KEYBOARD:KEY:ENTER"
  int firstColon = cmd.indexOf(':');
  int secondColon = cmd.indexOf(':', firstColon + 1);
  
  if (firstColon == -1 || secondColon == -1) {
    return;
  }
  
  String type = cmd.substring(firstColon + 1, secondColon);
  String value = cmd.substring(secondColon + 1);
  
  if (type == "CHAR") {
    // 文字入力: USB HIDキーボードとして送信
    sendKeyboardChar(value.charAt(0));
  } else if (type == "KEY") {
    // 特殊キー入力
    sendKeyboardKey(value);
  }
}

// 文字をUSB HIDキーボードとして送信
void sendKeyboardChar(char c) {
  // TODO: USB HIDキーボードライブラリを使用して実装
  // 例: Keyboard.write(c);
  // または、Switch用のHIDキーボードライブラリを使用
  
  Serial.print("Keyboard char: ");
  Serial.println(c);
  
  // 注意: 実際の実装では、USB HIDキーボードプロトコルを使用する必要があります
  // Arduino Leonardo/Micro/Pro Microなどの場合:
  // #include <Keyboard.h>
  // Keyboard.write(c);
  
  // ESP32などの場合、USB HIDライブラリが必要です
}

// 特殊キーをUSB HIDキーボードとして送信
void sendKeyboardKey(String key) {
  // TODO: USB HIDキーボードライブラリを使用して実装
  // 例: 
  // if (key == "ENTER") Keyboard.press(KEY_RETURN);
  // else if (key == "BACKSPACE") Keyboard.press(KEY_BACKSPACE);
  // ...
  
  Serial.print("Keyboard key: ");
  Serial.println(key);
  
  // 注意: 実際の実装では、USB HIDキーボードプロトコルを使用する必要があります
  // Arduino Leonardo/Micro/Pro Microなどの場合:
  // #include <Keyboard.h>
  // if (key == "ENTER") {
  //   Keyboard.press(KEY_RETURN);
  //   Keyboard.release(KEY_RETURN);
  // } else if (key == "BACKSPACE") {
  //   Keyboard.press(KEY_BACKSPACE);
  //   Keyboard.release(KEY_BACKSPACE);
  // }
  // ...
  
  // ESP32などの場合、USB HIDライブラリが必要です
}

