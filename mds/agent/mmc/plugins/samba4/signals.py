try:
    from mmc.core.signals import Signal
    share_created = Signal(providing_args=["share_name", "share_info"])
    share_modified = Signal(providing_args=["share_name", "share_info"])
except ImportError:
    # Old version mmc-core
    share_created = None
    share_modified = None
