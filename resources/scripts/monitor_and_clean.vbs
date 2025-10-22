Set WShell = CreateObject("WScript.Shell")
Set FSO = CreateObject("Scripting.FileSystemObject")

' Get arguments
Dim exePath, downloadsFolder
exePath = WScript.Arguments(0)
downloadsFolder = WScript.Arguments(1)
' Wait for webtorrent-runner.exe to terminate
Do
    Set processes = WShell.Exec("tasklist /FI ""IMAGENAME eq webtorrent-runner.exe""")
    processesStdOut = processes.StdOut.ReadAll
    If InStr(processesStdOut, "webtorrent-runner.exe") = 0 Then
        Exit Do
    End If
    WScript.Sleep 1000 ' Check every second
Loop

' Clean up downloads folder
If FSO.FolderExists(downloadsFolder) Then
    Set Folder = FSO.GetFolder(downloadsFolder)
    For Each File In Folder.Files
        On Error Resume Next
        FSO.DeleteFile File.Path, True
    Next
    For Each SubFolder In Folder.SubFolders
        On Error Resume Next
        FSO.DeleteFolder SubFolder.Path, True
    Next
End If