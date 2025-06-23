require("paulo.set")
require("paulo.remap")
require("paulo.lazy")
require("paulo.lsp")

-- loads theme
vim.cmd("colorscheme kanagawa")

function theme(themeName)
    require"kanagawa".load(themeName)
end
