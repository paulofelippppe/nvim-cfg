    local lspconfig = require("lspconfig")
    local cmpnvim = require("cmp_nvim_lsp")

    -- Setup of LSP
    local lspconfig_defaults = lspconfig.util.default_config
    lspconfig_defaults.capabilities =
        vim.tbl_deep_extend("force", lspconfig_defaults.capabilities, cmpnvim.default_capabilities())

    vim.api.nvim_create_autocmd("LspAttach", {
        desc = "LSP actions",
        callback = function(event)
            local opts = { buffer = event.buf }

            vim.keymap.set("n", "K", "<cmd>lua vim.lsp.buf.hover()<cr>", opts)
            vim.keymap.set("n", "gd", "<cmd>lua vim.lsp.buf.definition()<cr>", opts)
            vim.keymap.set("n", "gD", "<cmd>lua vim.lsp.buf.declaration()<cr>", opts)
            vim.keymap.set("n", "gi", "<cmd>lua vim.lsp.buf.implementation()<cr>", opts)
            vim.keymap.set("n", "go", "<cmd>lua vim.lsp.buf.type_definition()<cr>", opts)
            vim.keymap.set("n", "gr", "<cmd>lua vim.lsp.buf.references()<cr>", opts)
            vim.keymap.set("n", "gs", "<cmd>lua vim.lsp.buf.signature_help()<cr>", opts)
            vim.keymap.set("n", "<F2>", "<cmd>lua vim.lsp.buf.rename()<cr>", opts)
            vim.keymap.set({ "n", "x" }, "<F3>", "<cmd>lua vim.lsp.buf.format({async = true})<cr>", opts)
            vim.keymap.set("n", "<F4>", "<cmd>lua vim.lsp.buf.code_action()<cr>", opts)
        end,
    })

    -- Setup of mason (language server handler)
    require("mason").setup({})
    require("mason-lspconfig").setup({
        handlers = {
            function(server_name)
                lspconfig[server_name].setup({})
            end,
        },
        ensure_installed = {
            lua_ls = {},
            html = { filetypes = { 'html', 'php' }}
        },
        automatic_installation = true,
    })
    require("mason-tool-installer").setup({
        ensure_installed = {
         "phpstan",
         "cssls",
         "eslint-lsp",
         "eslint_d",
         "lua-language-server",
         "phpactor",
         "typescript-language-server",
        },
})

-- Setup autocompletion
local cmp = require("cmp")

cmp.setup({
	sources = {
		{ name = "nvim_lsp" },
	},
	mapping = {
		["<CR>"] = cmp.mapping(
			cmp.mapping.confirm({
				select = true,
				behavior = cmp.ConfirmBehavior.Insert,
			}),
			{ "i", "c" }
		),
		["<C-n>"] = cmp.mapping.select_next_item({
			behavior = cmp.ConfirmBehavior.Insert,
		}),
		["<C-p>"] = cmp.mapping.select_prev_item({
			behavior = cmp.ConfirmBehavior.Insert,
		}),
		["<C-u>"] = cmp.mapping.scroll_docs(-4),
		["<C-d>"] = cmp.mapping.scroll_docs(4),
	},
	snippet = {
		expand = function(args)
			vim.snippet.expand(args.body)
		end,
	},
})

-- Setup lua_ls
lspconfig.lua_ls.setup{
    settings = {
        Lua = {
            diagnostics = {
                disable = {
                    "missing-fields",
                    "lowercase-global"
                }
            }
        }
    }
}

-- Setup phpactor
lspconfig.phpactor.setup({
	cmd = { "phpactor", "language-server" },
	filetypes = { "php" },
	root_dir = lspconfig.util.root_pattern('.git', '.phpactor.json', '.phpactor.yml'),
	init_options = {
        ["language_server.diagnostics_on_update"] = false,
        ["language_server.diagnostics_on_open"] = false,
        ["language_server.diagnostics_on_save"] = false,
		["language_server_phpstan.enabled"] = true,
	},
})

-- Setup TS/JS language server
lspconfig.ts_ls.setup({
	init_options = {
		preferences = {
			includeInlayFunctionParameterTypeHints = true,
			includeInlayEnumMemberValueHints = true,
			includeInlayFunctionLikeReturnTypeHints = true,
			includeInlayVariableTypeHints = true,
			includeInlayPropertyDeclarationTypeHints = true,
			includeInlayParameterNameHints = "all",
		},
	},
})

-- Setup Eslint
lspconfig.eslint.setup({
	on_attach = function(client, bufnr)
		vim.api.nvim_create_autocmd("BufWritePre", {
			buffer = bufnr,
			command = "EslintFixAll",
		})
	end,
})

-- Setup HTML support
require('nvim-ts-autotag').setup({})
