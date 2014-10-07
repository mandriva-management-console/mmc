import logging
import time
from daemon import runner
from sync import sync_loop


class S4SyncApp(object):
    WAIT_TIME = 30  # sleep time between each iteration, in seconds

    def __init__(self, logger):
        self.stdin_path = '/dev/null'
        self.stdout_path = '/dev/null'
        self.stderr_path = '/dev/null'
        self.pidfile_path =  '/var/run/s4sync.pid'
        self.pidfile_timeout = 5

        self.logger = logger

    def run(self):
        sync_loop(self.logger, self.WAIT_TIME)


# Logging
logger = logging.getLogger("s4sync")
LOG_LEVEL = logging.DEBUG
handler = logging.FileHandler("/var/log/s4sync.log")
formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')
handler.setFormatter(formatter)
logger.addHandler(handler)
logger.setLevel(LOG_LEVEL)

# Python daemon preserving logger
app = S4SyncApp(logger)
daemon_runner = runner.DaemonRunner(app)
daemon_runner.daemon_context.files_preserve=[handler.stream]
daemon_runner.do_action()
