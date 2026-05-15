<x-mail::message>

Your OTP code is:
**{{ $otp }}**

This code will expire in {{ config('larotp.expiry_min') }} minutes. If you did not request for an OTP, you can safely ignore this email.

Do not share this code with anyone.

</x-mail::message>