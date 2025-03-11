return {
    "rebelot/kanagawa.nvim",
    config = function ()
        require"kanagawa".setup({
            theme = "wave",
            background = {
                dark = "dragon",
                light = "lotus",
            },
            colors = {
                theme = {
                    all = {
                        ui = {
                            bg_gutter = "none"
                        }
                    }
                }
            }
        })
    end
}

--return {
--    "catppuccin/nvim",
--    name = "catppuccin",
--    priority = 1000,
--    config = function()
--        require("catppuccin").setup({
--            flavour = "mocha"
--        })
--    end
--}
