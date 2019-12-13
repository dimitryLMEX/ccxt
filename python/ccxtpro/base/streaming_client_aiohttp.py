import json
from asyncio import sleep, ensure_future
from aiohttp import WSMsgType
from ccxt.async_support import Exchange
from ccxtpro.base.streaming_client import StreamingClient


class StreamingClientAiohttp(StreamingClient):

    def closed(self):
        return self.connection.closed

    def receive(self):
        return self.connection.receive()

    async def handle_message(self, message):
        print(Exchange.iso8601(Exchange.milliseconds()), '→', message)
        if message.type == WSMsgType.TEXT:
            print(Exchange.iso8601(Exchange.milliseconds()), 'message', message)
            data = message.data
            message = json.loads(data) if Exchange.is_json_encoded_object(data) else data
            self.on_message_callback(self, message)
        # elif message.type == WSMsgType.BINARY:
        #     print(Exchange.iso8601(Exchange.milliseconds()), 'binary', message)
        elif message.type == WSMsgType.PING:
            print(Exchange.iso8601(Exchange.milliseconds()), 'ping', message)
            self.connection.pong()
        elif message.type == WSMsgType.PONG:
            print(Exchange.iso8601(Exchange.milliseconds()), 'pong', message)
            print('Pong received')
        elif message.type == WSMsgType.CLOSE:
            print(Exchange.iso8601(Exchange.milliseconds()), 'close', message)
            ensure_future(self.close())
        elif message.type == WSMsgType.CLOSED:
            print(Exchange.iso8601(Exchange.milliseconds()), 'closed', message)
            # print(self.closed())
            # break  # stops the loop, call on_close
        elif message.type == WSMsgType.ERROR:
            print(Exchange.iso8601(Exchange.milliseconds()), 'error', message)
            # print(self.closed())
            # break  # stops the loop, call on_error

    def create_connection(self, session):
        return session.ws_connect(self.url)

    def send(self, message):
        print(Exchange.iso8601(Exchange.milliseconds()), 'sending', message)
        return self.connection.send_str(json.dumps(message, separators=(',', ':')))

    def close(self):
        return self.connection.close()

    async def ping_loop(self):
        print(Exchange.iso8601(Exchange.milliseconds()), 'ping loop')
        while not self.closed():
            #     if (self.lastPong + self.keepAlive) < Exchange.milliseconds():
            #         self.reset(RequestTimeout('Connection to ' + self.url + ' timed out due to a ping-pong keepalive missing on time'))
            #     else:
            #         if self.connection.readyState == WebSocket.OPEN:
            #             self.connection.ping()
            await sleep(self.keepAlive / 1000)
