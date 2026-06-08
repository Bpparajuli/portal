<div class="chat-footer">
    <div id="attachmentPreview">
        <div class="d-flex align-items-center gap-2">
            <span id="previewIcon"></span>
            <span id="fileNameDisplay" class="small fw-medium" style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></span>
        </div>
        <button type="button" class="btn-close btn-close-sm" onclick="cancelAttachment()"></button>
    </div>
    <div class="chat-input-row">
        <label class="chat-btn" role="button" title="Attach file">
            <i class="fas fa-paperclip"></i>
            <input type="file" id="fileInput" class="d-none" onchange="handleFileSelect(this)">
        </label>
        <input type="text" id="msgInput" placeholder="Type a message..." autocomplete="off" onkeydown="if(event.key==='Enter') send()" oninput="onTyping()">
        <button class="chat-btn send-btn" id="sendBtn" onclick="send()"><i class="fas fa-paper-plane"></i></button>
    </div>
</div>
