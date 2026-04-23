<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!--JQuery-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!--Toastr JS-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js" integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" integrity="sha512-vKMx8UnXk60zUwyUnUPM3HbQo8QfmNx7+ltw8Pm5zLusl1XIfwcxo8DbWCqMGKaWeNxWA8yrx5v3SaVpMvR3CA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div style="min-height: 100vh;">
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap');
            *{
                font-family: "Roboto", sans-serif;
                font-optical-sizing: auto;
                font-style: normal;
            }
            .input-container{
                width: 100%; 
                height: 100%; 
                display: flex; 
                align-items: center; 
                justify-content: center;
            }
            .input-inner{
                min-height: 300px; 
                background: #FFF; 
                border-radius: 5px; 
                padding: 20px; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                flex-direction: column;
            }
            .otp-inputs{
                display: flex;
                gap: 10px;
                justify-content: center;
            }
            .otp-field{
                width: 100%;
                height: 60px;
                font-size: 1.5rem;
                text-align: center;
                border: 1px solid #d1d5db;
                border-radius: 8px;
                outline: none;
                transition: border-color 0.2s;
                font-weight: 600;
                color: #1f2937;
            }
    
            .otp-field:focus {
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }
    
            .verify-button{
                width: 100%;
                padding: 15px;
                font-size: 24px;
            }
    
            .text-center{
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
            }
    
            #link_button{
                cursor: pointer;
                color: #3b82f6;
            }
        </style>
        <div class="input-container">
            <div class="input-inner">
                <h2 class="text-center">Enter OTP Code</h2>
                <p class="text-center">Enter the {{ $digits }} digit code sent to your email address: {{ $user->email }}</p>
                <span class="otp-inputs">
                    <form id="input">
                        
                        @csrf
                        <input type="text" maxlength="{{ $digits }}" class="otp-field" name="client_otp" autofocus>
    
                        <p class="text-center">Haven't received a code. <span id="resend_link"><a id="link_button"> Resend</a></span></p>
                        <p class="text-center" id="timer"></p>
                        <button class="verify-button" type="submit">Verify</button>
                    </form>
                </span>
            </div>
        </div>
    
        <script>
            document.addEventListener("DOMContentLoaded", ()=>{
                const verify_form = document.getElementById('input');
                const link_button = document.getElementById('link_button');
                let timerInstance = null;
                let cooldownTimer = 120; // 2 mins
                let otp_value = [];
                
                async function verifyOTP(otp_value){
                    const formData = new FormData(verify_form);
    
                    try {
                        const response = await fetch("{{ $url }}", {
                            method: "POST",
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await response.json();
    
                        if(data.verified === true){
                            notify(response, data);
                            setTimeout(() => {
                                window.location.href = data.redirect_url;
                            }, 2000);
                        }else{
                            notify(response, data);
                        }
                    } catch (e) {
                        toastr.error(e);
                        console.error(e);
                    }
                }
    
                async function regenerateOTP(){
                    const csrf_token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
                    if(!csrf_token){
                        toastr.error("Missing CSRF token");
                        return;
                    }
    
                    try{
                        const response = await fetch("{{ route('resend.otp') }}", {
                            method: "POST",
                            headers: {
                                'X-CSRF-TOKEN': csrf_token,
                                'Accept': 'application/json',
                            }
                        });
    
                        switchCooldownTimer();
                        const result = await response.json();
    
                        notify(response, result);
                        console.log("Success: ", result);
                    }catch(err){
                        toastr.error(`Error during request: ${err}`);
                        console.log("Error: ", err);
                    }
                }
    
                function switchCooldownTimer() {
                    const link = document.getElementById('resend_link');
                    const timerDiv = document.getElementById('timer');

                    clearTimeout(timerInstance);

                    if (cooldownTimer > 0) {
                        link.style.pointerEvents = "none";
                        link.style.color = "grey";

                        let mins = Math.floor(cooldownTimer / 60);
                        let secs = cooldownTimer % 60;

                        const displayMins = mins.toString().padStart(2, "0");
                        const displaySecs = secs.toString().padStart(2, "0");

                        timerDiv.innerText = `Wait for ${displayMins}:${displaySecs} to request another OTP`;

                        cooldownTimer--;

                        timerInstance = setTimeout(switchCooldownTimer, 1000);
                    } else {
                        link.style.pointerEvents = "auto";
                        link.style.color = "";
                        timerDiv.innerText = "";
                    }
                }

                function notify(response, results){
                    if(response.ok){
                        toastr.success(result.message || "Success");
                    }else{
                        if(response.status === 422 && results.errors){
                            Object.values(results.errors).forEach(err => toastr.error(err[0]));
                        }else{
                            toastr.error(results.message || "An error occured");
                        }
                    }
                };
    
                verify_form.addEventListener("submit", (event) => {
                    event.preventDefault();
                    verifyOTP(otp_value);
                });
    
                link_button.addEventListener("click", (event) => {
                    event.preventDefault();
                    regenerateOTP();
                });
                
            });
        </script>
        <!-- It is quality rather than quantity that matters. - Lucius Annaeus Seneca -->
    </div>
</body>
</html>
