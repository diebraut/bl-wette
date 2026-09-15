Option Explicit

' Task Scheduler runs this through wscript.exe, without a console window.
' Only the local XAMPP Apache and MySQL processes enable the update.
Dim service, processes, process, apacheRunning, mysqlRunning, shell, result
apacheRunning = False
mysqlRunning = False
On Error Resume Next
Set service = GetObject("winmgmts:\\.\root\cimv2")
If Err.Number <> 0 Then WScript.Quit 1
Set processes = service.ExecQuery("SELECT Name, ExecutablePath FROM Win32_Process WHERE Name='httpd.exe' OR Name='mysqld.exe'")
If Err.Number <> 0 Then WScript.Quit 1
For Each process In processes
    If Not IsNull(process.ExecutablePath) Then
        If LCase(process.ExecutablePath) = "k:\xampp\apache\bin\httpd.exe" Then apacheRunning = True
        If LCase(process.ExecutablePath) = "k:\xampp\mysql\bin\mysqld.exe" Then mysqlRunning = True
    End If
Next
If Err.Number <> 0 Then WScript.Quit 1
On Error GoTo 0
If Not (apacheRunning And mysqlRunning) Then WScript.Quit 0

Set shell = CreateObject("WScript.Shell")
result = shell.Run("""K:\xampp\php\php.exe"" ""K:\PyCharm-Projekte\bl-wette\bin\update_matches.php""", 0, True)
WScript.Quit result
