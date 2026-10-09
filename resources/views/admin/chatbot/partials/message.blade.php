@if($msg->origem == 'admin')
    <div class="message message-sent mb-3">
        <div class="d-flex flex-row-reverse align-items-end">
            <div class="bg-primary text-white rounded-4 px-3 py-2 ms-2" style="max-width: 75%;">
                {{ $msg->mensagem }}
                <small class="text-white-50 d-block text-end" style="font-size: 10px;">
                    {{ $msg->created_at->format('H:i') }}
                </small>
            </div>
            <div class="avatar-circle bg-primary bg-opacity-10">
                <i class="fas fa-user-shield text-primary"></i>
            </div>
        </div>
    </div>
@else
    <div class="message message-received mb-3">
        <div class="d-flex align-items-end">
            <div class="avatar-circle bg-info bg-opacity-10">
                <i class="fas fa-user text-info"></i>
            </div>
            <div class="bg-light rounded-4 px-3 py-2 ms-2" style="max-width: 75%;">
                {{ $msg->mensagem }}
                <small class="text-muted d-block" style="font-size: 10px;">
                    {{ $msg->created_at->format('H:i') }}
                </small>
            </div>
        </div>
    </div>
@endif