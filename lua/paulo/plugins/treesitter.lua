return {
    "nvim-treesitter/nvim-treesitter",
    event = { "BufReadPre", "BufNewFile" },
    build = ":TSUpdate",
    dependencies = {
        "windwp/nvim-ts-autotag",
    },
    config = function()
        local treesitter = require("nvim-treesitter.configs")

        treesitter.setup {
            ensure_installed = {
                "json",
                "javascript",
                "typescript",
                "tsx",
                "yaml",
                "html",
                "css",
                "markdown",
                "markdown_inline",
                "bash",
                "lua",
                "vim",
                "dockerfile",
                "gitignore",
                "c",
                "rust",
                "php",
            },

            sync_install = true,
            auto_install = true,
            ignore_install = {},

            highlight = {
                enable = true,

                -- NOTE: these are the names of the parsers and not the filetype. (for example if you want to
                -- disable highlighting for the `tex` filetype, you need to include `latex` in this list as this is
                -- the name of the parser)
                -- list of language that will be disabled
                --disable = { "c", "rust" },
                -- Or use a function for more flexibility, e.g. to disable slow treesitter highlight for large files
                disable = function(lang, buf)
                    local max_filesize = 100 * 1024 -- 100 KB
                    local ok, stats = pcall(vim.loop.fs_stat, vim.api.nvim_buf_get_name(buf))
                    if ok and stats and stats.size > max_filesize then
                        return true
                    end
                end,

                -- Setting this to true will run `:h syntax` and tree-sitter at the same time.
                -- Set this to `true` if you depend on 'syntax' being enabled (like for indentation).
                -- Using this option may slow down your editor, and you may see some duplicate highlights.
                -- Instead of true it can also be a list of languages
                additional_vim_regex_highlighting = false,
            },
        }
--        treesitter.setup({
--            highlight = {
--                enable = true,
--                additional_vim_regex_highlighting = false,
--            },
--            indent = { enable = true },
--            autotag = {
--                enable = true,
--            },
--            ensure_installed = {
--                "json",
--                "javascript",
--                "typescript",
--                "tsx",
--                "yaml",
--                "html",
--                "css",
--                "markdown",
--                "markdown_inline",
--                "bash",
--                "lua",
--                "vim",
--                "dockerfile",
--                "gitignore",
--                "c",
--                "rust",
--                "php",
--            },
--            incremental_selection = {
--                enable = true,
--                keymaps = {
--                    init_selection = "<C-space>",
--                    node_incremental = "<C-space>",
--                    scope_incremental = false,
--                    node_decremental = "<bs>",
--                },
--            },
--            rainbow = {
--                enable = true,
--                disable = { "html" },
--                extended_mode = false,
--                max_file_lines = nil,
--            },
--            context_commentstring = {
--                enable = true,
--                enable_autocmd = false,
--            },
--        })
    end,
}
