import re
import os

from mmc.plugins.shorewall.config import ShorewallPluginConfig


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
        f = open(self.path, 'r')
        lines = f.readlines()
        f.close()
        for line in lines:
            line = line.strip()
            result = re.match(self.pattern, line)
            if result:
                self.file.append(ShorewallLine(result.groups(), self.output_format))
            else:
                self.file.append(line)

    def write(self):
        f = open(self.path, 'w+')
        for line in self.file:
            f.write(str(line) + "\n")
        f.close()

    def add_line(self, values, position = None):
        new = ShorewallLine(values, self.output_format)
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
        old = ShorewallLine(old_values, self.output_format)
        new = ShorewallLine(new_values, self.output_format)
        for index, line in enumerate(self.file[:]):
            if str(line) == str(old):
                self.file[index] = new
                self.write()
                return True
        return False

    def del_line(self, values):
        delete = ShorewallLine(values, self.output_format)
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
        return [ line.get() for line in self.file if isinstance(line, ShorewallLine)]

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
