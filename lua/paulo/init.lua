require("paulo.set")
require("paulo.lazy")
require("paulo.remap")
require("paulo.lsp")

-- loads theme
vim.cmd("colorscheme kanagawa")

function theme(theme)
    local flavours = { lotus = 1, wave = 1, dragon = 1 }
    local theme_name = ""
    local file_path = vim.fn.stdpath("config") .. "/.theme"
    if flavours[theme] then
        local file = io.open(file_path, "w")
        file:write(theme)
        file:close()
        theme_name = theme
    else
        local file = io.open(file_path, "r")
        if not file then
            theme_name = 'dragon'
        else
            local filecontent = file:read()
            theme_name = flavours[filecontent] and filecontent or 'dragon'
        end
    end
    require"kanagawa".load(theme_name)
end

theme()
