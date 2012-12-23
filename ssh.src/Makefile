CXX=g++
CC=g++
CXXFLAGS=-Wall -O2
all: ssh
ssh: ssh.o
ssh.o: ssh.cc

.PHONY: clean install
install:
	cp ssh ..
	chown root ../ssh
	chmod +s ../ssh
clean:
	rm -rf ssh ssh.o