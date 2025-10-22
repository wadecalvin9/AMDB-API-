Set WShell = CreateObject("WScript.Shell")

' Log arguments for debugging
Dim i, arg
For i = 0 To WScript.Arguments.Count - 1
    WScript.Echo "Argument " & i & ": " & WScript.Arguments(i)
Next

' Run the first argument (the full command)
WShell.Run WScript.Arguments(0), 0, False

WScript.Echo "Command executed: " & WScript.Arguments(0)