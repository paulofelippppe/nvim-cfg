require("paulo.set")
require("paulo.lazy")
require("paulo.remap")
require("paulo.lsp")

-- loads theme
vim.cmd("colorscheme kanagawa")

function theme(themeName)
    require"kanagawa".load(themeName)
end
