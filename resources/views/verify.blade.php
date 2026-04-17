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
    </style>
    <div class="input-container">
        <div class="input-inner">
            <h2 class="text-center">Enter OTP Code</h2>
            <p class="text-center">Enter the {{ $digits }} digit code sent to your email address: {{ $user->email }}</p>
            <span class="otp-inputs">
                <form id="input">
                    
                    @csrf
                    <input type="text" maxlength="{{ $digits }}" class="otp-field" name="client_otp" autofocus>

                    <p class="text-center">Haven't recieved a code. <a href="{{ route('resend.otp') }}">Resend</a></p>
                    <button class="verify-button" type="submit">Verify</button>
                </form>
            </span>
        </div>
    </div>

    <script>
        const verify_form = document.getElementById('input');
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
                    window.location.href = data.redirect_url;
                }else{
                    alert(data.message || "Invalid OTP");
                }
            } catch (e) {
                console.error(e);
            }
        }

        verify_form.addEventListener("submit", (event) => {
            event.preventDefault();
            verifyOTP(otp_value);
        });
    </script>
    <!-- It is quality rather than quantity that matters. - Lucius Annaeus Seneca -->
</div>

