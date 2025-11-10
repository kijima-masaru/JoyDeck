<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class MicrocontrollerService
{
    protected $connectionType;
    protected $isConnected = false;
    protected $connection = null;

    // 接続タイプ: 'serial', 'usb'
    const CONNECTION_SERIAL = 'serial';
    const CONNECTION_USB = 'usb';

    public function __construct()
    {
        $this->connectionType = Config::get('microcontroller.connection_type', self::CONNECTION_SERIAL);
    }

    /**
     * マイコンに接続
     */
    public function connect(): bool
    {
        try {
            switch ($this->connectionType) {
                case self::CONNECTION_SERIAL:
                    return $this->connectSerial();
                case self::CONNECTION_USB:
                    return $this->connectUsb();
                default:
                    Log::error("Unknown connection type: {$this->connectionType}");
                    return false;
            }
        } catch (\Exception $e) {
            Log::error("Connection error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * シリアル接続
     */
    protected function connectSerial(): bool
    {
        $port = Config::get('microcontroller.serial_port', 'COM3');
        $baudRate = Config::get('microcontroller.baud_rate', 115200);

        try {
            // Windowsの場合
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windowsでは "\\\\.\\COM3" 形式で開く必要がある
                $portName = $port;
                if (!preg_match('/^\\\\\\\\.\\\\/', $portName)) {
                    $portName = '\\\\.\\' . $port;
                }
                
                // COMポートを開く
                $handle = @fopen($portName, 'r+');
                if ($handle === false) {
                    Log::error("Failed to open serial port: {$port}");
                    return false;
                }
                
                // シリアルポートの設定（Windowsではmodeコマンドを使用）
                $this->configureSerialPortWindows($port, $baudRate);
                
                $this->connection = $handle;
                $this->isConnected = true;
                Log::info("Serial connection established to {$port} at {$baudRate} baud");
                return true;
            } else {
                // Linux/Macの場合
                // /dev/ttyACM0, /dev/ttyUSB0, /dev/ttyUSB1 などを試す
                $possiblePorts = [$port];
                if ($port === 'COM3' || $port === 'COM1') {
                    // デフォルト値の場合は、一般的なLinux/Macポートを試す
                    $possiblePorts = ['/dev/ttyACM0', '/dev/ttyUSB0', '/dev/ttyUSB1'];
                    
                    // Macの場合、/dev/tty.usbmodem* を検索
                    if (strtoupper(substr(PHP_OS, 0, 6)) === 'DARWIN') {
                        $usbModemPorts = glob('/dev/tty.usbmodem*');
                        if ($usbModemPorts) {
                            $possiblePorts = array_merge($usbModemPorts, $possiblePorts);
                        }
                    }
                }
                
                foreach ($possiblePorts as $tryPort) {
                    if (file_exists($tryPort) && is_readable($tryPort)) {
                        // sttyコマンドでシリアルポートを設定
                        $this->configureSerialPortUnix($tryPort, $baudRate);
                        
                        // シリアルポートを開く
                        $handle = @fopen($tryPort, 'r+');
                        if ($handle !== false) {
                            $this->connection = $handle;
                            $this->isConnected = true;
                            Log::info("Serial connection established to {$tryPort} at {$baudRate} baud");
                            return true;
                        }
                    }
                }
                
                Log::error("Failed to open serial port: {$port} (not found or not accessible)");
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Serial connection error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Windowsでシリアルポートを設定
     */
    protected function configureSerialPortWindows(string $port, int $baudRate): void
    {
        // Windowsのmodeコマンドでシリアルポートを設定
        // mode COM3: BAUD=115200 PARITY=N DATA=8 STOP=1
        $portNumber = preg_replace('/[^0-9]/', '', $port);
        if ($portNumber) {
            $command = sprintf(
                'mode COM%s: BAUD=%d PARITY=N DATA=8 STOP=1',
                $portNumber,
                $baudRate
            );
            @exec($command . ' 2>&1', $output, $returnCode);
            if ($returnCode === 0) {
                Log::info("Serial port configured: {$command}");
            } else {
                Log::warning("Failed to configure serial port with mode command");
            }
        }
    }

    /**
     * Unix/Linux/Macでシリアルポートを設定
     */
    protected function configureSerialPortUnix(string $port, int $baudRate): void
    {
        // sttyコマンドでシリアルポートを設定
        $command = sprintf(
            'stty -F %s %d cs8 -cstopb -parenb raw -echo 2>&1',
            escapeshellarg($port),
            $baudRate
        );
        @exec($command, $output, $returnCode);
        if ($returnCode === 0) {
            Log::info("Serial port configured: {$port} at {$baudRate} baud");
        } else {
            Log::warning("Failed to configure serial port with stty (may require permissions)");
        }
    }

    /**
     * USB接続
     */
    protected function connectUsb(): bool
    {
        // USB接続の実装
        // 実際の実装では、libusbなどのライブラリを使用
        Log::info("USB connection attempted");
        
        // TODO: 実際のUSB接続実装
        $this->isConnected = true;
        return true;
    }

    /**
     * マイコンから切断
     */
    public function disconnect(): void
    {
        if ($this->connection && is_resource($this->connection)) {
            fclose($this->connection);
        }
        
        $this->connection = null;
        $this->isConnected = false;
        Log::info("Disconnected from microcontroller");
    }

    /**
     * コマンドを送信
     */
    public function sendCommand(string $command): bool
    {
        if (!$this->isConnected) {
            Log::warning("Cannot send command: not connected");
            return false;
        }

        try {
            switch ($this->connectionType) {
                case self::CONNECTION_SERIAL:
                    // シリアルポートへの書き込み
                    if ($this->connection && is_resource($this->connection)) {
                        $result = fwrite($this->connection, $command . "\n");
                        if ($result === false) {
                            Log::error("Failed to write to serial port");
                            $this->disconnect();
                            return false;
                        }
                        // データが確実に送信されるようにフラッシュ
                        fflush($this->connection);
                        Log::debug("Sent command via serial: {$command}");
                        return true;
                    } else {
                        Log::error("Serial port connection is not available");
                        return false;
                    }
                case self::CONNECTION_USB:
                    // USBへの書き込み
                    // TODO: 実装
                    Log::info("Sending command via USB: {$command}");
                    return true;
            }
        } catch (\Exception $e) {
            Log::error("Error sending command: " . $e->getMessage());
            return false;
        }

        return false;
    }

    /**
     * 接続状態を取得
     */
    public function isConnected(): bool
    {
        return $this->isConnected;
    }

    /**
     * 接続タイプを設定
     */
    public function setConnectionType(string $type): void
    {
        $this->connectionType = $type;
    }
}

