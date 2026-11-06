local bridge = {}
local php

function bridge.setupInterface()
    bridge.setupInterface = nil
    php = mw_interface
    mw_interface = nil
    mw = mw or {}
    mw.neowikibase = bridge
    package.loaded['mw.neowikibase'] = bridge
end

-- No IDs are required: defaults to the current page's Main Subject.
function bridge.getFields(options)
    return php.getFields(options)
end

return bridge
