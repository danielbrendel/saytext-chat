<div class="headline">
	<div>{{ env('APP_SERVERNAME', 'Unnamed server') }}</div>
	<div>{!! BBCode::transform(env('APP_SERVERTOPIC', 'No server topic set')) !!}</div>
</div>

<div class="container">
	<div class="saytext-chat" style="background-image: url('{{ asset('img/backgrounds/background' . rand(1, $max_backgrounds) . '.png') }}');">
		<div class="saytext-chat-overlay">
			<div class="saytext-chat-left">
				<div class="saytext-chat-online" onclick="window.toggleUserList();"></div>

				<div class="saytext-chat-content"></div>

				<div class="saytext-chat-actions">
					<div class="saytext-chat-options">
						<div class="saytext-chat-options-item" onclick="window.localCommand('/timestamps ' + ((window.showTimestamps) ? 'off' : 'on'));">
							<img id="saytext-chat-option-timestamps" src="{{ asset('img/icons/timestamps_on.png') }}" alt="icon"/>
						</div>

						<div class="saytext-chat-options-item" onclick="window.localCommand('/switchbg');">
							<img src="{{ asset('img/icons/switchbg.png') }}" alt="icon"/>
						</div>

						<div class="saytext-chat-options-item" onclick="window.localCommand('/sound ' + ((window.soundEnable) ? 'off' : 'on'));">
							<img id="saytext-chat-option-sound" src="{{ asset('img/icons/sound_on.png') }}" alt="icon"/>
						</div>
					</div>

					<div class="saytext-chat-actions-message">
						<form onsubmit="event.preventDefault(); window.chatMessage(); this.children[0].value = '';">
							<input class="chat-input-element" type="text" placeholder="Enter a message..."/>
						</form>
					</div>

					<div class="saytext-chat-actions-settings">
						<input class="chat-input-element" type="text" placeholder="Choose your name" onchange="localStorage.setItem('cl_name', this.value);" value="{{ ($username ?? '') }}"/>

						<select class="chat-input-element" title="Choose a team" onchange="localStorage.setItem('cl_team', this.value);">
							<option value="spec" {{ (((empty($userteam)) || ($userteam === 'spec')) ? 'selected' : '') }}>Spectator</option>
							<option value="t" {{ (($userteam === 't') ? 'selected' : '') }}>Terrorists</option>
							<option value="ct" {{ (($userteam === 'ct') ? 'selected' : '') }}>Counter-Terrorists</option>
						</select>
					</div>
				</div>
			</div>

			<div class="saytext-chat-right">
				<div class="saytext-chat-list"></div>
			</div>
		</div>
	</div>
</div>
