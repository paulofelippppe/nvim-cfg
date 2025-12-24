-- Disables the cursor-styling for each mode (insert, visual, etc.)
vim.opt.guicursor = ""

-- Print the line number in front of each line
vim.opt.nu = true

-- Changes the displayed number to be relative to the cursor
vim.opt.relativenumber = true

-- Defines the column multiple used to display the Horizontal Tab character (ASCII 9)
vim.opt.tabstop = 4

-- Defines the width of soft tabs
vim.opt.softtabstop = 4

-- Number of columns that make up one level of (auto)indentation
vim.opt.shiftwidth = 4

-- Uses the appropriate number of spaces to insert a <Tab>
vim.opt.expandtab = true

-- Overriding css/scss two spaces tab
vim.api.nvim_create_autocmd("FileType", {
    pattern = { "css", "scss" },
    callback = function()
        vim.opt_local.tabstop = 4
        vim.opt_local.softtabstop = 4
        vim.opt_local.shiftwidth = 4
        vim.opt_local.expandtab = true
    end,
})

-- Do smart autoindenting when starting a new line
vim.opt.smartindent = true

-- Disables text highlighting when searching for patterns in the file
vim.opt.hlsearch = false

-- Highlights the first match when searching for patterns in the file
vim.opt.incsearch = true

-- Disables Neovim's builtin file explorer
vim.g.loaded_netrw = 1
vim.g.loaded_netrwPlugin = 1

-- Enables 24-bit RGB color in the TUI
vim.opt.termguicolors = true

-- Minimal number of screen lines to keep above and below the cursor
vim.opt.scrolloff = 8

-- Always showing the sign column (for errors or warnings) even if blank
vim.opt.signcolumn = "yes"

-- "is file name" - list of characters and ranges that Neovim treats as valid filename characters
-- In this case appends a range of @ to @ (which means that supports filenames with at sign, beyond
-- the default Neovim settings)
vim.opt.isfname:append("@-@")

-- After 50 milliseconds of no typing, Neovim saves the changes to the swap file.
-- It can be recovered by `nvim -r <file>`
vim.opt.updatetime = 50

-- Set the <leader> key to a blank space
vim.g.mapleader = " "

-- Splitting a window will put the new window right of the current one (using :vsplit)
vim.o.splitright = true

-- Wraps text
vim.o.wrap = true

