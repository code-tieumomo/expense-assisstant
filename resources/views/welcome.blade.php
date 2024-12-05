<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles / Scripts -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-black text-white/50 overflow-hidden">
    <div class="bg-black text-white/50">
        <img id="background" class="absolute -left-20 top-0 max-w-[877px]"
            src="https://laravel.com/assets/img/welcome/background.svg" alt="Laravel background" />
        <div
            class="relative min-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
            <div class="relative w-full max-w-2xl p-4 h-screen lg:max-w-7xl">
                <main class="h-full w-full">
                    <div id="docs-card"
                        class="h-full w-full flex flex-col items-start gap-6 rounded-lg p-4 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 transition duration-300 focus:outline-none bg-zinc-900 ring-zinc-800 hover:text-white/70 hover:ring-zinc-700 focus-visible:ring-[#FF2D20]">
                        <ul id="list"
                            class="scrollbar-hide grow space-y-4 w-full overflow-x-hidden overflow-y-auto scroll-smooth">
                        </ul>
                        <form x-data="chat" x-on:submit="submit"
                            class="w-full flex items-end rounded-lg shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 transition duration-300 ring-zinc-800 hover:ring-zinc-700 focus-visible:ring-[#FF2D20] p-2">
                            <textarea rows="1" class="grow bg-transparent focus:outline-none text-sm resize-none" placeholder="Nhập ở đây ..."
                                x-model="message"></textarea>
                            <button type="submit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 14 14">
                                    <path fill="currentColor" fill-rule="evenodd"
                                        d="M11.821.098a1.62 1.62 0 0 1 2.077 2.076l-3.574 10.712a1.62 1.62 0 0 1-1.168 1.069a1.6 1.6 0 0 1-1.52-.434l-1.918-1.909l-2.014 1.042a.5.5 0 0 1-.73-.457l.083-3.184l7.045-5.117a.625.625 0 1 0-.735-1.012L2.203 8.088l-1.73-1.73a1.6 1.6 0 0 1-.437-1.447a1.62 1.62 0 0 1 1.069-1.238h.003L11.82.097Z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('chat', () => ({
                messages: [{
                        id: 1,
                        content: 'Ăn bún hết 25k',
                        isBot: false
                    },
                    {
                        id: 2,
                        content: 'Đã ghi nhận giao dịch, cảm ơn bạn!',
                        isBot: true
                    }
                ],
                message: '',
                list: null,

                init() {
                    document.querySelectorAll('textarea').forEach(function(textarea) {
                        textarea.style.height = textarea.scrollHeight + 'px';
                        textarea.style.overflowY = 'hidden';

                        textarea.addEventListener('input', function() {
                            this.style.height = 'auto';
                            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
                            this.parentNode.previousElementSibling.style.maxHeight =
                                'calc(100% - ' + (Math.min(this.scrollHeight, 120) +
                                    30) + 'px)';
                        });
                    });

                    this.list = document.getElementById('list');

                    this.render();
                },

                render() {
                    let prevItem;
                    this.messages.forEach((message) => {
                        let li = document.getElementById('message-' + message.id);
                        if (!li) {
                            li = document.createElement('li');
                            li.className = 'w-full flex items-baseline gap-2' + (!message
                                .isBot ? ' justify-end' : '');
                            li.id = 'message-' + message.id;
                            li.innerHTML = message.isBot ?
                                `
                                    <div class='shrink-0 w-6 h-6 rounded-full flex items-center justify-center bg-[#FF2D20] text-white text-xs'>
                                        $
                                    </div>
                                    <div class='p-2 bg-zinc-800 rounded-lg text-sm'>
                                        ${message.content}
                                    </div>
                                ` :
                                `
                                    <div class='p-2 bg-zinc-800 rounded-lg text-sm'>
                                        ${message.content}
                                    </div>
                                    <div class='shrink-0 w-6 h-6 rounded-full flex items-center justify-center bg-[#FF2D20] text-white text-xs'>
                                        U
                                    </div>
                                `;
                            this.list.appendChild(li);
                        } else {
                            if (prevItem) {
                                prevItem.after(li);
                            } else {
                                this.list.prepend(li);
                            }
                        }
                        prevItem = li;
                    });

                    this.list.scrollTop = this.list.scrollHeight;
                },

                async submit(e) {
                    e.preventDefault();
                    if (this.message.trim() === '') {
                        return;
                    }

                    this.messages.push({
                        id: this.messages.length + 1,
                        content: this.message,
                        isBot: false
                    });

                    const lastMessage = this.message;
                    this.message = '';

                    this.render();

                    const response = await fetch(
                        '/api/chat',
                        {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                message: lastMessage
                            })
                        }
                    );
                    const transaction = await response.json();
                    this.messages.push({
                        id: this.messages.length + 1,
                        content: `Đã ghi nhận giao dịch ${transaction.type} (${transaction.category}). Tổng cộng ${transaction.amount.toLocaleString()} VND`,
                        isBot: true
                    });

                    this.render();
                }
            }))
        })
    </script>
</body>

</html>
