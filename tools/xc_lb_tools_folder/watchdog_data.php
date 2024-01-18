<?php
//for lb
if (!@$argc) {
    die(0);
}

require "/home/xtreamcodes/iptv_xtream_codes/wwwdir/init.php";
shell_exec("kill \$(ps aux | grep 'Server WatchDog' | grep -v grep | grep -v " . getmypid() . " | awk '{print $2}')");
while (!false) {
    // chatgpt wrote the part that converts xml to json output
    // Get XML data using shell_exec or handle the case where nvidia-smi is not available
    if (file_exists("/usr/bin/nvidia-smi")) {
        $xml = shell_exec("nvidia-smi -x -q");

        if ($xml === null || empty($xml) || strpos($xml, "NVIDIA-SMI has failed") !== false) {
            // Handle the case where nvidia-smi is  returned an error
            // You can set default values or display an error message.
            $jsonOutput = [ "nvidia_gpu"  => [ "status" => "1", "error" => empty($xml) ? "NVIDIA-SMI is not available or returned an error." : $xml ]];    
        } else {
            // Proceed with the XML parsing and JSON conversion as before
            $xmlData = simplexml_load_string($xml);

            // Initialize the JSON structure
            $jsonOutput = [
                "nvidia_gpu" => [
                    "driver_version" => (string)$xmlData->driver_version,
                    "cuda_version" => (float)$xmlData->cuda_version,
                    "attached_gpus" => (int)$xmlData->attached_gpus,
                    "gpu" => []
                ]
            ];

        // Extract data for each GPU
            foreach ($xmlData->gpu as $gpu) {
                $gpuData = [
                    "id" => (int)$gpu->attributes()->id,
                    "gpu_name" => (string)$gpu->product_name,
                    "persistence_mode" => (string)$gpu->persistence_mode,
                    "serial_no" => (int)$gpu->serial,
                    "uuid" => (string)$gpu->uuid,
                    "vbios_version" => (string)$gpu->vbios_version,
                    "system_reboot_required" => (string)$gpu->gpu_reset_status->reset_required,
                    "pci_bus_id" => (string)$gpu->pci->pci_bus_id,
                    "fb_memory_usage" => [
                        "total" => (string)$gpu->fb_memory_usage->total,
                        "reserved" => (string)$gpu->fb_memory_usage->reserved,
                        "used" => (string)$gpu->fb_memory_usage->used,
                        "free" => (string)$gpu->fb_memory_usage->free
                    ],
                    "utilization" => [
                        "gpu_util" => (string)$gpu->utilization->gpu_util,
                        "memory_util" => (string)$gpu->utilization->memory_util,
                        "encoder_util" => (string)$gpu->utilization->encoder_util,
                        "decoder_util" => (string)$gpu->utilization->decoder_util
                    ],
                    "encoder_stats" => [
                        "session_count" => (int)$gpu->encoder_stats->session_count,
                        "average_fps" => (int)$gpu->encoder_stats->average_fps,
                        "average_latency" => (int)$gpu->encoder_stats->average_latency
                    ],
                    "fan_speed" => (string)$gpu->fan_speed,
                    "gpu_temp" => (string)$gpu->temperature->gpu_temp,
                    "performance_state" => (string)$gpu->performance_state,
                    "power_draw" => (string)$gpu->gpu_power_readings->power_draw,
                    "current_power_limit" => (string)$gpu->gpu_power_readings->current_power_limit,
                    "clocks" => [
                        "graphics_clock" => (string)$gpu->clocks->graphics_clock,
                        "sm_clock" => (string)$gpu->clocks->sm_clock,
                        "mem_clock" => (string)$gpu->clocks->mem_clock,
                        "video_clock" => (string)$gpu->clocks->video_clock
                    ],
                    "graphics_voltage" => (string)$gpu->voltage->graphics_volt,
                    "processes" => [
                        "process_info" => []
                    ]
                ];

                // Extract and append process information
                foreach ($gpu->processes->process_info as $processInfo) {
                    $processData = [
                        "pid" => (int)$processInfo->pid,
                        "process_name" => (string)$processInfo->process_name,
                        "used_memory" => (string)$processInfo->used_memory
                    ];

                    $gpuData["processes"]["process_info"][] = $processData;
                }

                // Append GPU data to the JSON structure
                $jsonOutput["nvidia_gpu"]["gpu"][] = $gpuData;
            }
        }
    } else {
        $jsonOutput = ["nvidia_gpu"  => [ "status" => "0", "error" => "NVIDIA-SMI couldnt found. if you have a gpu on the server, install the driver first."]];
    }
    $wd_data = array_merge(C2e1Ca22613b17bf832806eB2f8E484c(),$jsonOutput); 
    if (!$F30ccc8fd3427f97ca35bc6ba6610d01->query('UPDATE `streaming_servers` SET `watchdog_data` = \'%s\' WHERE `id` = \'%d\'', json_encode($wd_data, JSON_PARTIAL_OUTPUT_ON_ERROR), SERVER_ID)) {
        break;
    }
    sleep(2);
}

unset($wd_data);
shell_exec('(sleep 1; ' . PHP_BIN . ' ' . __FILE__ . ' ) > /dev/null 2>/dev/null &');
?>