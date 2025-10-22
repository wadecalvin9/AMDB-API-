Set WShell = CreateObject("WScript.Shell")
On Error Resume Next
WShell.Run WScript.Arguments(0), 0, False
If Err.Number <> 0 Then
    Set FSO = CreateObject("Scripting.FileSystemObject")
    Set LogFile = FSO.OpenTextFile("C:\path\to\storage\logs\vbscript.log", 8, True)
    LogFile.WriteLine "Error: " & Err.Description
    LogFile.Close
End If