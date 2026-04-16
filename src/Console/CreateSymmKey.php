<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp\Console;

use Exception;
use Illuminate\Console\Command;
use InvalidArgumentException;

class CreateSymmKey extends Command{
    protected $signature = "larotp:generatekey";

    protected $description = "Generates a base64 encoded key using randombyte function";

    protected $key_length = 32;

    public function handle(){
        $this->info("Generating symmetric key........");

        $key = $this->createSecretKey();
        
        $this->newLine();
        $this->line("|-----------------------------------------------------------|");
        $this->line("│ Generated Symmetric Secret Key:                           │");
        $this->line("|-----------------------------------------------------------|");
        $this->newLine();
        $this->info($key);
        $this->newLine();
    }

    protected function createSecretKey(){
        try{
            if(!isset($this->key_length)){
                throw new InvalidArgumentException(
                    "Config 'key_length' is not set in larotp config"
                );
            }
            $key_length = $this->key_length;

            if(!is_int($key_length)){
                throw new InvalidArgumentException(
                    "Config 'key_lengh' must be an int. ".gettype($key_length)." given"
                );
            }

            $random_bytes = random_bytes($key_length);
            $converted_key = bin2hex($random_bytes);

            //The key is encoded using base64
            $encoded_key = base64_encode($converted_key);
            $string = $encoded_key;

            return $string;
        }catch(\Throwable $e){
            error_log("Unexpected error in CreateSecretKey: ".$e->getMessage());
            throw new Exception(
                "An unexpected error occurred while generating secret key",
                0,
                $e
            );
        }
    }
}