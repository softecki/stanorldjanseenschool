<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laravel ChatGPT</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; padding: 2rem; background-color: #f9f9f9; }
        .chat-box { max-width: 600px; margin: auto; background: #fff; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .chat-message { margin-bottom: 1rem; }
        .user { font-weight: bold; color: #2c3e50; }
        .assistant { color: #16a085; }
        .form-group { display: flex; gap: 1rem; margin-top: 1.5rem; }
        input[type="text"] { flex: 1; padding: 0.5rem; font-size: 1rem; border: 1px solid #ccc; border-radius: 5px; }
        button { padding: 0.5rem 1rem; font-size: 1rem; background: #3498db; color: #fff; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #2980b9; }
    </style>
</head>
<body>
    <div class="chat-box">
        <h2>Ask ChatGPT</h2>

        @if(session('reply'))
            <div class="chat-message">
                <div class="user">You:</div>
                <p>{{ session('prompt') }}</p>
            </div>
            <div class="chat-message">
                <div class="assistant">ChatGPT:</div>
                <p>{{ session('reply') }}</p>
            </div>
        @endif

        <form action="/chat" method="POST">
            @csrf
            <div class="form-group">
                <input type="text" name="prompt" placeholder="Type your message..." required>
                <button type="submit">Send</button>
            </div>
        </form>
    </div>
</body>
</html>
