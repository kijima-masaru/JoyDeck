<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class MicrocontrollerService
{
    protected $connectionType;
    protected $isConnected = false;
    protected $connection = null;

    // 接続タイプ: 'serial', 'tcp', 'usb'
    const CONNECTION_SERIAL = 'serial';
    const CONNECTION_TCP = 'tcp';
    const CONNECTION_USB = 'usb';

    public function __construct()
    {
        $this->connectionType = Config::get('microcontroller.connection_type', self::CONNECTION_TCP);
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
                case self::CONNECTION_TCP:
                    return $this->connectTcp();
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
        // シリアルポート接続の実装
        // Windows: COMポート、Linux/Mac: /dev/ttyUSB0など
        $port = Config::get('microcontroller.serial_port', 'COM3');
        $baudRate = Config::get('microcontroller.baud_rate', 115200);

        // 実際の実装では、php-serialなどのライブラリを使用
        // ここでは接続成功をシミュレート
        Log::info("Serial connection attempted to {$port} at {$baudRate} baud");
        
        // TODO: 実際のシリアル接続実装
        $this->isConnected = true;
        return true;
    }

    /**
     * TCP接続
     */
    protected function connectTcp(): bool
    {
        $host = Config::get('microcontroller.tcp_host', '127.0.0.1');
        $port = Config::get('microcontroller.tcp_port', 8888);

        try {
            $socket = @fsockopen($host, $port, $errno, $errstr, 5);
            
            if ($socket === false) {
                Log::error("TCP connection failed: {$errstr} ({$errno})");
                return false;
            }

            $this->connection = $socket;
            $this->isConnected = true;
            Log::info("TCP connection established to {$host}:{$port}");
            return true;
        } catch (\Exception $e) {
            Log::error("TCP connection error: " . $e->getMessage());
            return false;
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
                case self::CONNECTION_TCP:
                    if ($this->connection && is_resource($this->connection)) {
                        $result = fwrite($this->connection, $command . "\n");
                        if ($result === false) {
                            Log::error("Failed to write to TCP socket");
                            $this->disconnect();
                            return false;
                        }
                        return true;
                    }
                    break;
                case self::CONNECTION_SERIAL:
                    // シリアルポートへの書き込み
                    // TODO: 実装
                    Log::info("Sending command via serial: {$command}");
                    return true;
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

