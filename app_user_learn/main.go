package main

import (
	"bytes"
	"os"
	"os/exec"
	"fmt"
	"sync"
	"strconv"
)


/**
 * 多协程并发的执行任务
 * 为保证并发安全，执行的php脚本里处理的资源应该是相互隔离的
 * go run main.go demo.php 100
 */
var wg sync.WaitGroup

// job
func execShell(file string, i int) {
	defer wg.Done() // goroutine结束就登记-1
	fmt.Printf("job%d start doing...\n", i)
	var out bytes.Buffer
	// arg := string(i)
	arg := fmt.Sprintf("%d", i)
	cmd := exec.Command("/usr/local/php/bin/php", file, arg)
	cmd.Stdout = &out
	err := cmd.Run()
	if err != nil {
		panic(err)
	}
	fmt.Printf("执行输出 %s \n", out.String())
	fmt.Printf("job%d end\n", i)
}

// main
func main()  {
	path := os.Args[1]
	jobNum := os.Args[2]

	num, _ := strconv.Atoi(jobNum)
	fmt.Printf("执行的文件是：%s \n", path)
	fmt.Printf("开启的协程数是：%d \n", num)
	for i := 0; i < num; i++ {
		wg.Add(1) // 启动一个goroutine就登记+1
		go execShell(path, i)
	}

	wg.Wait() // 等待所有登记的goroutine都结束
}