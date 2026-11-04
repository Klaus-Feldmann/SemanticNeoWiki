-- Read-only adapter to the shared PHP field resolver; no annotation trigger.
local p = {}
function p.collect(options)
    local nw = require('mw.neowiki')
    if type(nw.collectFields) ~= 'function' then
        error('NeoWiki/API: Die gemeinsame PHP-Werteschnittstelle fehlt.', 0)
    end
    return nw.collectFields(options or {})
end
return p
