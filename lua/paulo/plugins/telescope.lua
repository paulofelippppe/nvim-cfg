return {
    "nvim-telescope/telescope.nvim",
    tag = "v0.20.0",
    dependencies = { "nvim-lua/plenary.nvim" },
    config = function()
        require("telescope").setup({
            defaults = {
                preview = {
                    treesitter = false
                }
            }
        })
    end,
}
