return {
    "neovim/nvim-lspconfig",
	name = "nvim-lspconfig",
    event = { "BufReadPre", "BufNewFile" },
    dependencies = {
        "hrsh7th/cmp-nvim-lsp",
        "hrsh7th/nvim-cmp",
        { "folke/neodev.nvim", opts = {} },
    }
}
