import re
import os
import logging

from mmc.plugins.shorewall.config import ShorewallPluginConfig


logger = logging.getLogger(__name__)


class ShorewallLineInvalid(Exception):
    pass


class ShorewallLine:

    def __init__(self, values, output_format=None):
        self._line = values
        self._output_format = output_format

    def get(self):
        return self._line

    def __getitem__(self, k):
        return self._line[k]

    def __str__(self):
        if not self._output_format:
            l = ""
            for item in self._line:
                l += item + "\t"
            return l
        else:
            return self._output_format % tuple(self._line)


class ShorewallConf:

    def __init__(self, file, pattern, output_format=None):
        self.conf = ShorewallPluginConfig('shorewall')
        self.path = os.path.join(self.conf.path, file)
        self.pattern = pattern
        self.output_format = output_format
        self.file = []

    def read(self):
        logger.debug("Parsing %s" % self.path)
        with open(self.path, 'r') as h:
            for line in h:
                line = line.strip()
                result = re.match(self.pattern, line)
                if result:
                    self.file.append(ShorewallLine(result.groups(''), self.output_format))
                    logger.debug(result.groupdict(''))
                else:
                    self.file.append(line)
                    logger.debug("Line '%s' skipped" % line)

    def write(self):
        f = open(self.path, 'w+')
        for line in self.file:
            f.write(str(line) + "\n")
        f.close()

    def validate(self, line):
        """Override for extra validation.
        Raise ShorewallLineInvalid with an error message."""
        pass

    def validate_line(self, values):
        line = ShorewallLine(values, self.output_format)
        result = re.match(self.pattern, str(line).strip())
        if not result:
            raise ShorewallLineInvalid("Invalid shorewall line")
        self.validate(result.groupdict())
        return line

    def add_line(self, values, position=None):
        new = self.validate_line(values)
        # remove identic lines first
        self.del_line(values)
        if position is not None:
            fake_index = 0
            for index, line in enumerate(self.file):
                if isinstance(line, ShorewallLine):
                    if fake_index == position:
                        self.file.insert(index, new)
                    fake_index += 1
        else:
            self.file.append(new)
        self.write()
        return True

    def replace_line(self, old_values, new_values):
        old = self.validate_line(old_values)
        new = self.validate_line(new_values)
        for index, line in enumerate(self.file[:]):
            if str(line) == str(old):
                self.file[index] = new
                self.write()
                return True
        return False

    def del_line(self, values):
        delete = self.validate_line(values)
        for index, line in enumerate(self.file[:]):
            if str(line) == str(delete):
                del self.file[index]
                self.write()
                return True
        return False

    def get_line(self, position):
        fake_index = 0
        for index, line in enumerate(self.file):
            if isinstance(line, ShorewallLine):
                if fake_index == position:
                    return line.get()
                fake_index += 1
        return False

    def get_conf(self):
        return [line.get() for line in self.file if isinstance(line, ShorewallLine)]

    def set_conf(self, conf):
        file = []
        for line in conf:
            file.append(ShorewallLine(line, self.output_format))
        self.file = file
        self.write()
        return True


if __name__ == "__main__":
    conf = ShorewallConf('/etc/shorewall/interfaces', r'^(?P<zone>[\w\d]+)\s+(?P<interface>[\w\d]+)')
    conf.read()
    conf.add_line(["foo", "eth2"])
    print "-----"
    conf.add_line(["foo1", "eth3"], 0)
    print "-----"
    print conf.get_line(0)
    print "-----"
    conf.set_conf([['lan', 'eth0'], ['wan', 'eth1']])
    print "-----"
    tmp = conf.get_conf()
    tmp.append(['foo', 'eth2'])
    conf.set_conf(tmp)
    print "-----"
    conf = ShorewallConf('/etc/shorewall/policy', r'^(?P<src>[\w\d]+)\s+(?P<dst>[\w\d]+)\s+(?P<policy>[\w]+)\s*(?P<log>[\w]*)')
    conf.read()
    conf.write()
    print "-----"
    conf = ShorewallConf('/etc/shorewall/zones', r'^(?P<name>[\w\d]+)\s+(?P<type>[\w\d]+)')
    conf.read()
    conf.write()
    print "-----"
    conf = ShorewallConf('/etc/shorewall/rules', r'^(?P<action>[\w\d/]+)\s+(?P<src>[\w\d]+)\s+(?P<dst>[\w\d]+)\s*(?P<proto>[\w\d]*)\s*(?P<dst_port>[\w\d]*)')
    conf.read()
    conf.write()
