<?php
require_once '../config/config.php';
require_once '../models/User.php';

include 'includes/header.php';

$user_id = $_SESSION['user_id'];
$target_user_id = $_GET['dealer_id'] ?? ($_GET['tenant_id'] ?? null);
$property_id = $_GET['property_id'] ?? null;
?>

<div class="container-fluid py-4 h-100 d-flex flex-column" style="max-height: calc(100vh - 80px);">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-1 fw-bold">Messages</h4>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden flex-grow-1 h-100 d-flex flex-row">
        
        <!-- Sidebar Contacts -->
        <div class="border-end bg-light d-flex flex-column" style="width: 320px;">
            <div class="p-3 border-bottom bg-white">
                <input type="text" class="form-control rounded-pill" placeholder="Search contacts..." id="searchContacts">
            </div>
            <div class="list-group list-group-flush flex-grow-1 overflow-auto" id="contactsList">
                <div class="text-center p-4 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading...
                </div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="flex-grow-1 d-flex flex-column bg-white position-relative">
            <!-- Chat Header -->
            <div class="p-3 border-bottom d-flex align-items-center bg-white z-1" id="chatHeader" style="display: none !important;">
                <img src="../assets/images/user-placeholder.png" class="rounded-circle me-3" width="45" height="45" id="activeChatAvatar" style="object-fit: cover;">
                <div>
                    <h6 class="mb-0 fw-bold" id="activeChatName">Select a conversation</h6>
                    <small class="text-muted" id="activeChatRole"></small>
                </div>
            </div>

            <!-- Messages List -->
            <div class="flex-grow-1 p-4 overflow-auto" id="messagesArea" style="background: #f8f9fa;">
                <div class="h-100 d-flex align-items-center justify-content-center text-muted flex-column">
                    <i class="bi bi-chat-dots fs-1 mb-2"></i>
                    <p>Select a contact to start chatting</p>
                </div>
            </div>

            <!-- Chat Input -->
            <div class="p-3 border-top bg-white" id="chatInputArea" style="display: none;">
                <form id="chatForm" class="d-flex align-items-end gap-2">
                    <input type="hidden" id="receiverId" name="receiver_id" value="<?php echo htmlspecialchars($target_user_id); ?>">
                    <input type="hidden" id="propertyId" name="property_id" value="<?php echo htmlspecialchars($property_id); ?>">
                    
                    <textarea class="form-control rounded-4 bg-light border-0" id="messageInput" name="message" rows="1" placeholder="Type a message..." style="resize: none;"></textarea>
                    
                    <button type="submit" class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; flex-shrink: 0;" id="sendBtn">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.message-bubble {
    max-width: 75%;
    padding: 10px 16px;
    border-radius: 1rem;
    margin-bottom: 8px;
    word-wrap: break-word;
}
.msg-sent {
    background-color: #0d6efd;
    color: white;
    border-bottom-right-radius: 4px;
    align-self: flex-end;
}
.msg-received {
    background-color: #e9ecef;
    color: #212529;
    border-bottom-left-radius: 4px;
    align-self: flex-start;
}
.contact-item {
    cursor: pointer;
    transition: background-color 0.2s;
}
.contact-item:hover, .contact-item.active {
    background-color: #e9ecef;
}
</style>

<script>
const currentUserId = <?php echo $user_id; ?>;
let activeContactId = <?php echo $target_user_id ? $target_user_id : 'null'; ?>;
let chatRefreshInterval;

function loadContacts() {
    fetch('../api/chat.php?action=contacts')
        .then(r => r.json())
        .then(data => {
            if(!data.success) return;
            const list = document.getElementById('contactsList');
            if(data.contacts.length === 0 && !activeContactId) {
                list.innerHTML = '<div class="text-center p-4 text-muted">No conversations yet.</div>';
                return;
            }
            
            let html = '';
            let activeContactExists = false;
            
            data.contacts.forEach(c => {
                if(c.id == activeContactId) activeContactExists = true;
                const avatar = c.profile_image ? '../' + c.profile_image : 'https://ui-avatars.com/api/?name='+encodeURIComponent(c.name);
                const isUnread = c.unread_count > 0 ? `<span class="badge bg-danger rounded-pill">${c.unread_count}</span>` : '';
                const time = c.last_time ? new Date(c.last_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : '';
                
                html += `
                <div class="list-group-item border-0 border-bottom contact-item d-flex align-items-center p-3 ${activeContactId == c.id ? 'active bg-light' : ''}" onclick="selectContact(${c.id}, '${c.name.replace(/'/g, "\\'")}', '${avatar}', '${c.role}')">
                    <img src="${avatar}" class="rounded-circle me-3 object-fit-cover" width="45" height="45">
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="mb-0 fw-bold text-truncate">${c.name}</h6>
                            <small class="text-muted" style="font-size: 0.7rem;">${time}</small>
                        </div>
                        <p class="mb-0 small text-muted text-truncate">${c.last_message || 'Start chatting...'}</p>
                    </div>
                    <div class="ms-2">
                        ${isUnread}
                    </div>
                </div>`;
            });
            list.innerHTML = html;
            
            // If activeContactId is set but not in list, it's a new conversation, we need to fetch their basic info
            if(activeContactId && !activeContactExists) {
                // For simplicity, we just select them directly if we know the ID from URL
                selectContact(activeContactId, 'New Contact', 'https://ui-avatars.com/api/?name=Contact', '');
            }
        });
}

function selectContact(id, name, avatar, role) {
    activeContactId = id;
    document.getElementById('receiverId').value = id;
    
    // Update Header
    document.getElementById('chatHeader').style.setProperty('display', 'flex', 'important');
    document.getElementById('chatInputArea').style.display = 'block';
    document.getElementById('activeChatName').innerText = name;
    document.getElementById('activeChatAvatar').src = avatar;
    document.getElementById('activeChatRole').innerText = role ? role.charAt(0).toUpperCase() + role.slice(1) : '';
    
    // Update Contacts UI
    document.querySelectorAll('.contact-item').forEach(el => el.classList.remove('active', 'bg-light'));
    loadContacts(); // Refresh to clear unread badge and set active class
    
    loadMessages();
    
    if(chatRefreshInterval) clearInterval(chatRefreshInterval);
    chatRefreshInterval = setInterval(loadMessages, 3000);
}

function loadMessages() {
    if(!activeContactId) return;
    
    fetch(`../api/chat.php?action=fetch&contact_id=${activeContactId}`)
        .then(r => r.json())
        .then(data => {
            if(!data.success) return;
            const area = document.getElementById('messagesArea');
            let html = '<div class="d-flex flex-column">';
            
            if(data.messages.length === 0) {
                html += '<div class="text-center text-muted my-auto"><p>No messages yet. Send a message to start.</p></div>';
            }
            
            data.messages.forEach(m => {
                const isSent = m.sender_id == currentUserId;
                const time = new Date(m.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                
                html += `
                <div class="message-bubble ${isSent ? 'msg-sent' : 'msg-received'}">
                    ${m.message.replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\\n/g, "<br>")}
                    <div class="text-end mt-1" style="font-size: 0.65rem; opacity: 0.8;">
                        ${time}
                    </div>
                </div>`;
            });
            html += '</div>';
            
            // Only auto-scroll if we're near the bottom to not interrupt reading
            const isScrolledToBottom = area.scrollHeight - area.clientHeight <= area.scrollTop + 50;
            
            area.innerHTML = html;
            
            if (isScrolledToBottom) {
                area.scrollTop = area.scrollHeight;
            }
        });
}

document.getElementById('chatForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const input = document.getElementById('messageInput');
    const msg = input.value.trim();
    if(!msg || !activeContactId) return;
    
    const formData = new FormData(this);
    
    // Optimistic UI update
    const area = document.getElementById('messagesArea');
    const container = area.querySelector('.d-flex.flex-column') || area;
    container.innerHTML += `
        <div class="message-bubble msg-sent opacity-50">
            ${msg.replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\\n/g, "<br>")}
        </div>`;
    area.scrollTop = area.scrollHeight;
    
    input.value = '';
    
    fetch('../api/chat.php?action=send', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            loadMessages();
            loadContacts();
        } else {
            alert('Failed to send message: ' + (data.error || 'Unknown error'));
        }
    });
});

// Auto-expand textarea
const textarea = document.getElementById('messageInput');
textarea.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = (this.scrollHeight) + 'px';
    if(this.value === '') this.style.height = 'auto';
});

// Press enter to send (shift+enter for new line)
textarea.addEventListener('keydown', function(e) {
    if(e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('chatForm').dispatchEvent(new Event('submit'));
    }
});

// Init
loadContacts();
if(activeContactId) {
    // We don't have the user's name immediately, it will be handled in loadContacts
}
</script>

<?php include 'includes/footer.php'; ?>