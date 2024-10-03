local pixTalkAction = TalkAction("!pix")
local PixGenerator = dofile('data/libs/polopag/pix_class.lua')

function pixTalkAction.onSay(player, words, param)
    local pixGen = PixGenerator(player:getAccountId())
    pixGen:sendPixValuesModalWindow(player)
end

pixTalkAction:groupType("normal")
pixTalkAction:register()
