import json
import os
import time
import hashlib
import hmac
import soco
import requests

os.environ['SONOS_ENDPOINT'] = 'https://vormkracht10-app.test'
os.environ['SONOS_SECRET'] = 'superduperdeultrauberdeluxewachtwoord'


class SonosData:
    def __init__(self):
        self.speakers = soco.discover()

    def run(self):
        tracks = self.get_now_playing_tracks()
        self.send_tracks_to_webhooks(tracks)

    def get_now_playing_tracks(self):
        now_playing = {}
        for speaker in self.speakers:
            state = speaker.get_current_transport_info()
            if state['current_transport_state'] == 'PLAYING':
                track_info = speaker.get_current_track_info()
                duration = track_info['duration']
                if not duration.isdigit():
                    duration = '0'
                now_playing[speaker.ip_address] = {
                    'room': speaker.player_name,
                    'state': state['current_transport_state'],
                    'volume': speaker.volume,
                    'title': track_info['title'],
                    'artist': track_info['artist'],
                    'album': track_info['album'],
                    'duration': duration,
                    'position': track_info['position'],
                    'cover': track_info['album_art'],
                    'timestamp': int(time.time())
                }
        return now_playing

    def send_tracks_to_webhooks(self, tracks):
        json_data = json.dumps({'speakers': tracks})
        print(json_data)
        secret = os.getenv('SONOS_SECRET')
        endpoint = os.getenv('SONOS_ENDPOINT') + '/webhooks/sonos'

        try:
            hash_signature = hmac.new(secret.encode(), json_data.encode(), hashlib.sha256).hexdigest()

            response = requests.post(endpoint, json=json_data, headers={'X-Signature': hash_signature}, verify=False)

            status_code = response.status_code

            if status_code == 200:
                print('Endpoint successfully sent to', endpoint)
                print('Body:', response.text)
        except Exception as e:
            print('Endpoint failed to send to', endpoint)
            print('Error occurred:', str(e))

sonos_data = SonosData()
sonos_data.run()
