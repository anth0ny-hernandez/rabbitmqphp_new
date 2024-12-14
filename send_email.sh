#!/bin/bash

recipient="$1"
subject="MealandMe: Two-Factor Authentication"
message="$2"

sendemail -f anthleal22@gmail.com -t "$recipient" -u "$subject" -m "Here is your one-time passcode. The code will expire in 1 minute. Don't share it with anyone:\n$message" -s smtp.gmail.com:587 -o tls=yes -xu anthleal22@gmail.com -xp "iwjw bgvl ewsf dhrg"

echo "Email successfully sent to $recipient"
