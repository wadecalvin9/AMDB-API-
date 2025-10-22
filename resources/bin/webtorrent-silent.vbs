Set WshShell = CreateObject("WScript.Shell")
WshShell.Run "resources\bin\webtorrent-runner.exe " & WScript.Arguments(0) & " --vlc", 0, False
