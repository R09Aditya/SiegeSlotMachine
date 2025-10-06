document.addEventListener("DOMContentLoaded", () => {
    const playerName = sessionStorage.getItem('name') || 'Player';
    document.getElementById('playerName').innerText = playerName;

    const coinCountElem = document.getElementById('coinCount');
    let coins = parseInt(sessionStorage.getItem('coins')) || 10;
    coinCountElem.innerText = coins;

    const reels = [
        document.getElementById('reel1').querySelector('img'),
        document.getElementById('reel2').querySelector('img'),
        document.getElementById('reel3').querySelector('img')
    ];

    const spinBtn = document.getElementById('spinBtn');
    const resultMsg = document.getElementById('resultMsg');

    spinBtn.addEventListener('click', () => {
        if (coins <= 0) {
            resultMsg.innerText = "Not enough coins!";
            return;
        }

        fetch('slot.php', { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                resultMsg.innerText = data.error;
                return;
            }


            for (let i = 0; i < 3; i++) {
                reels[i].src = `symbols/${data.slots[i]}.png`;
            }

            coins = data.coins;
            coinCountElem.innerText = coins;

            if (data.win > 0) {
                resultMsg.innerText = `You won ${data.win} coin(s)! 🎉`;
            } else {
                resultMsg.innerText = `No match. Try again!`;
            }

            sessionStorage.setItem('coins', coins);
        })
        .catch(err => {
            console.error(err);
            resultMsg.innerText = "Error spinning the slots!";
        });
    });
});
