// phone input mask
const phone = document.getElementById('phone');

if(phone) {
    phone.addEventListener('input', function(e) {
        let input = e.target.value;

        input = input.replace(/[^\d]/g, '');

        if (input.length > 0 && input.charAt(0) !== '1') {
            input = '1' + input;
        }

        if (input.length > 1 && input.length <= 4) {
            input = `+1 ${input.substring(1)}`;
        } else if (input.length > 4 && input.length <= 7) {
            input = `+1 ${input.substring(1, 4)}-${input.substring(4)}`;
        } else if (input.length > 7 && input.length <= 9) {
            input = `+1 ${input.substring(1, 4)}-${input.substring(4, 7)}-${input.substring(7, 9)}`;
        }else if (input.length > 9) {
            input = `+1 ${input.substring(1, 4)}-${input.substring(4, 7)}-${input.substring(7, 9)}-${input.substring(9, 11)}`;
        }

        e.target.value = input;
    });
}

