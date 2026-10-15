/*
    app.js
*/

import './../sass/app.scss';

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.chatServerName = '';
window.chatServerTopic = '';

window.chatFetchDelay = 10000;
window.chatOnlineDelay = 10000;
window.lastMessageId = null;
window.speakBetweenDelay = 100;

window.welcomeMessages = [
    {
        message: 'Okay, let\'s go!',
        sound: 'welcome1.wav'
    },

    {
        message: 'Go, go, go!',
        sound: 'welcome2.wav'
    },

    {
        message: 'Lock and load!',
        sound: 'welcome3.wav'
    },

    {
        message: 'Alright, let\'s move out!',
        sound: 'welcome4.wav'
    }
];

window.radioCommands = [
    {
        audio: 'affirmative',
        message: 'Affirmative!'
    },

    {
        audio: 'assistance',
        message: 'Taking fire, need assistance!'
    },

    {
        audio: 'blow',
        message: 'Get out of there, it\'s gonna blow!'
    },

    {
        audio: 'enemydown',
        message: 'Enemy down!'
    },

    {
        audio: 'fallback',
        message: 'Team, fall back!'
    },

    {
        audio: 'follow',
        message: 'Follow me!'
    },

    {
        audio: 'gogogo',
        message: 'Go go go!'
    },

    {
        audio: 'hole',
        message: 'Fire in the hole!'
    },

    {
        audio: 'negative',
        message: 'Negative!'
    },

    {
        audio: 'position',
        message: 'Hold this position!'
    },

    {
        audio: 'regroup',
        message: 'Regroup, team!'
    },

    {
        audio: 'report',
        message: 'Report in, team!'
    },

    {
        audio: 'roger',
        message: 'Roger that!'
    },

    {
        audio: 'rush',
        message: 'Storm the front!'
    },

    {
        audio: 'sector',
        message: 'Sector clear!'
    },

    {
        audio: 'spotted',
        message: 'Enemy spotted!'
    },

    {
        audio: 'takepoint',
        message: 'You take the point!'
    }
];

window.ajaxRequest = function(method, url, data = {}, successfunc = function(data){}, finalfunc = function(){}, config = {}) {
    let func = window.axios.get;
    if (method == 'post') {
        func = window.axios.post;
    } else if (method == 'patch') {
        func = window.axios.patch;
    } else if (method == 'delete') {
        func = window.axios.delete;
    }

    func(url, data, config)
        .then(function(response){
            successfunc(response.data);
        })
        .catch(function (error) {
            console.log(error);
        })
        .finally(function(){
                finalfunc();
            }
        );
};

window.addChatMessage = function(user, message, team, style) {
    let chat = document.querySelector('.saytext-chat-content');

    if (message.content.startsWith('/radio ')) {
        const radio = message.content.substr(message.content.indexOf(' ') + 1);
        const item = window.findRadioCommand(radio);

        if (item) {
            message.content = '(RADIO) ' + item.message;
            style = 'chat-style-notice';

            if (window.lastMessageId !== null) {
                window.playAudio(item.audio + '.wav');
            }
        } else {
            window.listRadioCommands();
        }
    } else if (message.content.startsWith('/speak ')) {
        const phrase = message.content.substr(message.content.indexOf(' ') + 1);

        if (phrase) {
            message.content = '(VOICE) ' + window.ucfirst(phrase) + '.';
            style = 'chat-style-notice';

            if ((window.lastMessageId !== null) && (window.soundEnable)) {
                window.speakText(phrase);
            }
        }
    }

    let msg = document.createElement('div');
    msg.classList.add(style);
    msg.innerText = ((window.showTimestamps) ? '[' + message.date + ']' : '') + '[' + team.toUpperCase() + '] ' + user + ': ' + message.content;
    chat.appendChild(msg);

    chat.scrollTop = chat.scrollHeight;
};

window.addNoticeMessage = function(message, style = 'chat-style-notice') {
    let chat = document.querySelector('.saytext-chat-content');

    let msg = document.createElement('div');
    msg.classList.add(style);
    msg.innerHTML = '** ' + message;
    chat.appendChild(msg);

    chat.scrollTop = chat.scrollHeight;
};

window.netChatMessage = function(user, team, msg) {
    window.ajaxRequest('post', window.location.origin + '/chat/message', { username: user, team: team, message: msg }, function(response) {
        if (response.code == 200) {
            window.fetchMessages();
        } else {
            window.addNoticeMessage('Error: ' + response.msg);
        }
    });
};

window.chatMessage = function() {
    const parent = document.querySelector('.saytext-chat-actions');
    const msg = parent.children[1].children[0].children[0].value;
    const user = parent.children[2].children[0].value;
    const team = parent.children[2].children[1].value;

    window.currentChatMessage = msg;
    window.currentUsername = user;
    window.currentTeam = team;

    if (window.localCommand(msg)) {
        return;
    }

    window.netChatMessage(user, team, msg);
};

window.fetchMessages = function() {
    window.ajaxRequest('post', window.location.origin + '/chat/fetch', { from: window.lastMessageId }, function(response) {
        if (response.code == 200) {
            if (response.messages.length > 0) {
                response.messages.forEach(function(msg) {
                    if (msg.context === 'user') {
                        window.addChatMessage(msg.username, { content: msg.message, date: msg.date }, msg.team, 'chat-style-' + msg.team);
                    } else {
                        window.addNoticeMessage(msg.message);
                    }
                });

                window.lastMessageId = response.messages[response.messages.length - 1].id;
            }
        } else {
            //window.addNoticeMessage('Error: ' + response.msg);
        }
    });
}

window.onlineCount = function() {
    window.ajaxRequest('post', window.location.origin + '/chat/online', {}, function(response) {
        if (response.code == 200) {
            let elem = document.querySelector('.saytext-chat-online');
            if (elem) {
                if (response.count > 1) {
                    elem.innerHTML = `${response.count} chatters`;
                } else {
                    elem.innerHTML = `You're alone`;
                }
            }
        } else {
            window.addNoticeMessage('Error: ' + response.msg);
        }
    });
};

window.onlineUsers = function() {
    window.ajaxRequest('post', window.location.origin + '/chat/users', {}, function(response) {
        if (response.code == 200) {
            let elem = document.querySelector('.saytext-chat-list');
            if (elem) {
                elem.innerHTML = '';

                for (let i = 0; i < response.users.length; i++) {
                    let color = '#ffd000';
                    if (response.users[i].team == 'spec') {
                        color = '#ffffff';
                    } else if (response.users[i].team == 'ct') {
                        color = '#95c8ff';
                    } else if (response.users[i].team == 't') {
                        color = '#df4c4c';
                    }

                    elem.innerHTML += `<div class="saytext-chat-list-item"><span style="color: ${color};">${response.users[i].name}</span></div>`;
                }
            }
        } else {
            window.addNoticeMessage('Error: ' + response.msg);
        }
    });
};

window.findRadioCommand = function(token) {
    for (let i = 0; i < window.radioCommands.length; i++) {
        if (window.radioCommands[i].audio === token) {
            return window.radioCommands[i];
        }
    }

    return null;
};

window.listRadioCommands = function() {
    window.addNoticeMessage('List of available radio commands:');

    for (let i = 0; i < window.radioCommands.length; i++) {
        window.addNoticeMessage(window.radioCommands[i].audio + ' -> ' + window.radioCommands[i].message)
    }
};

window.speakText = function(text) {
    const words = text.split(' ');
    window.spkSoundList = [];

    window.spkLoadedAudioCount = 0;
    window.spkRequiredAudioCount = words.length;

    for (let i = 0; i < words.length; i++) {
        const audio = new Audio(window.location.origin + '/snd/voice/' + words[i] + '.wav');
        audio.onloadeddata = function() { window.spkLoadedAudioCount++; };
        window.spkSoundList.push(audio);
    }

    window.spkCurrentAudioInterval = setInterval(function() {
        if (window.spkLoadedAudioCount === window.spkRequiredAudioCount) {
            window.speakList();

            clearInterval(window.spkCurrentAudioInterval);
        }
    }, 100);
};

window.speakList = function() {
    window.spkListCounter = 0;

    const cb = function() {
        window.spkSoundList[window.spkListCounter].onended = function() {
            window.spkListCounter++;

            if (window.spkListCounter < window.spkRequiredAudioCount) {
                setTimeout(cb, window.speakBetweenDelay);
            }
        };

        window.spkSoundList[window.spkListCounter].play();
    };

    setTimeout(cb, 10);
};

window.playAudio = function(soundfile) {
    if (!window.soundEnable) {
        return;
    }

    let audio = new Audio(window.location.origin + '/snd/' + soundfile);
    audio.onloadeddata = function() {
        audio.play();
    };
};

window.switchBackground = function() {
    const elem = document.querySelector('.saytext-chat');
    elem.style.backgroundImage = `url('${window.location.origin}/img/backgrounds/background${window.random(1, window.chatMaxBackgrounds)}.png')`;
};

window.localCommand = function(expression) {
    if (expression.indexOf('/') === 0) {
        expression = expression.substr(1);
        
        if (expression === 'switchbg') {
            window.switchBackground();

            window.addNoticeMessage('Changed background image');

            return true;
        } else if (expression.indexOf('leet ') === 0) {
            const msg = window.leetspeak(expression.substr(expression.indexOf(' ') + 1));
            
            window.netChatMessage(window.currentUsername, window.currentTeam, msg);

            return true;
        } else if (expression.indexOf('sound ') === 0) {
            const value = expression.substr(expression.indexOf(' ') + 1).toLowerCase();
            let sndUpdate = true;

            if (value === 'on') {
                window.soundEnable = 1;
            } else if (value === 'off') {
                window.soundEnable = 0;
            } else if (value === 'status') {
                sndUpdate = false;
                window.addNoticeMessage(((window.soundEnable) ? 'Sound is currently enabled' : 'Sound is currently disabled'));
            } else {
                window.addNoticeMessage('Error: Choose either \'on\', \'off\' or \'status\'');
                sndUpdate = false;
            }

            if (sndUpdate) {
                localStorage.setItem('s_enable', window.soundEnable);
                window.addNoticeMessage('Sound is now: ' + ((window.soundEnable) ? 'On' : 'Off'));

                let sndIcon = document.querySelector('#saytext-chat-option-sound');
                if (sndIcon) {
                    sndIcon.src = window.location.origin + '/img/icons/sound_' + ((window.soundEnable) ? 'on' : 'off') + '.png';
                }
            }

            return true;
        } else if (expression.indexOf('timestamps ') === 0) {
            const value = expression.substr(expression.indexOf(' ') + 1).toLowerCase();
            let tmUpdate = true;

            if (value === 'on') {
                window.showTimestamps = 1;
            } else if (value === 'off') {
                window.showTimestamps = 0;
            } else if (value === 'status') {
                tmUpdate = false;
                window.addNoticeMessage(((window.showTimestamps) ? 'Timestamps are currently enabled' : 'Timestamps are currently disabled'));
            } else {
                window.addNoticeMessage('Error: Choose either \'on\', \'off\' or \'status\'');
                tmUpdate = false;
            }

            if (tmUpdate) {
                localStorage.setItem('cl_timestamps', window.showTimestamps);
                window.addNoticeMessage('Timestamps are now: ' + ((window.showTimestamps) ? 'On' : 'Off'));

                let tsIcon = document.querySelector('#saytext-chat-option-timestamps');
                if (tsIcon) {
                    tsIcon.src = window.location.origin + '/img/icons/timestamps_' + ((window.showTimestamps) ? 'on' : 'off') + '.png';
                }
            }

            return true;
        } else if (expression.indexOf('radio list') === 0) {
            window.addNoticeMessage('List of radio commands:');

            for (let i = 0; i < window.radioCommands.length; i++) {
                window.addNoticeMessage(`#${i + 1} [${window.radioCommands[i].audio}] ${window.radioCommands[i].message}`);
            }

            return true;
        }
    }

    return false;
};

window.random = function(min, max) {
    return Math.floor(Math.random() * (max - min + 1) + min);
};

window.ucfirst = function(text) {
    return String(text).charAt(0).toUpperCase() + String(text).slice(1);
};

window.leetspeak = function(msg) {
    const alphabet = ["4", "8", "<", "|)", "3", "ƒ", "9", "]-[", "!", "_|", "|<", "£", "/\\/\\", "|\\|", "0", "|²", "q", "|2", "$", "7", "µ", "V", "\\/\\/", "}{", "¥", "z"];

    let result = '';

    for (let i = 0; i < msg.length; i++) {
        const c = msg[i].toLowerCase().charCodeAt() - 97;
        if ((c >= 0) && (c < alphabet.length)) {
            result += alphabet[c];
        } else {
            result += msg[i];
        }
    }

    return result;
};

window.toggleUserList = function() {
    const list = document.querySelector('.saytext-chat-right');
    const content = document.querySelector('.saytext-chat-left');

    if (list.style.display === 'none') {
        list.style.display = 'inline-block';
        list.style.width = '20%';

        content.style.width = '80%';
    } else {
        list.style.display = 'none';
        list.style.width = '0%';

        content.style.width = '100%';
    }
};

document.addEventListener('DOMContentLoaded', function() {
    window.chatterName = localStorage.getItem('cl_name');
    window.chatterTeam = localStorage.getItem('cl_team');
    window.soundEnable = localStorage.getItem('s_enable');
    window.showTimestamps = localStorage.getItem('cl_timestamps');

    if (window.chatterName !== null) {
        document.querySelector('.saytext-chat-actions-settings').children[0].value = window.chatterName;
    }

    if (window.chatterTeam !== null) {
        document.querySelector('.saytext-chat-actions-settings').children[1].value = window.chatterTeam;
    }

    if (window.soundEnable !== null) {
        window.soundEnable = parseInt(window.soundEnable);
    } else {
        window.soundEnable = 1;
    }

    let sndIcon = document.querySelector('#saytext-chat-option-sound');
    if (sndIcon) {
        sndIcon.src = window.location.origin + '/img/icons/sound_' + ((window.soundEnable) ? 'on' : 'off') + '.png';
    }

    if (window.showTimestamps !== null) {
        window.showTimestamps = parseInt(window.showTimestamps);
    } else {
        window.showTimestamps = 0;
    }

    let tsIcon = document.querySelector('#saytext-chat-option-timestamps');
    if (tsIcon) {
        tsIcon.src = window.location.origin + '/img/icons/timestamps_' + ((window.showTimestamps) ? 'on' : 'off') + '.png';
    }

    if (window.chatServerName.length > 0) { window.addNoticeMessage(window.chatServerName); }
    if (window.chatServerTopic.length > 0) { window.addNoticeMessage(window.chatServerTopic); }

    const rndnum = window.random(0, window.welcomeMessages.length - 1);
    window.addNoticeMessage(window.welcomeMessages[rndnum].message);
    window.playAudio(window.welcomeMessages[rndnum].sound);

    window.fetchTimer = setInterval(function() {
        window.fetchMessages();
    }, window.chatFetchDelay);

    window.onlineTimer = setInterval(function() {
        window.onlineCount();
    }, window.chatOnlineDelay);

    window.usersTimer = setInterval(function() {
        window.onlineUsers();
    }, window.chatOnlineDelay);

    setTimeout(function() {
        window.fetchMessages();
    }, 100);

    setTimeout(function() {
        window.onlineCount();
    }, 100);

    setTimeout(function() {
        window.onlineUsers();
    }, 100);
});
