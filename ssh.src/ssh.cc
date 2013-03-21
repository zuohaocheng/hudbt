#include <iostream>
#include <fstream>
#include <string>
#include <iterator>
#include <cctype>
#include <ctime>

using namespace std;

int main(int argc, char *argv[]) {
  if (!argv[1]) {
    cout << "No IPv4 address specified" << endl;
    return 1;
  }
  string ip(argv[1]);
  for (string::iterator i = ip.begin(); i!=ip.end(); ++i) {
    if (!isdigit(*i) && *i != '.') {
      cout << "Invalid IPv4 address" << endl;
      return 1;
    }
  }

  string file;
  string line;
  //TODO: check privilege & file existion
  fstream f("/etc/hosts.allow", ios::in | ios::out);
  bool found_sshd = false;
  bool add_ip = true;
  while (!f.eof()) {
    getline(f, line);
    if (line.find("sshd") != string::npos) {
      found_sshd = true;
      if (line.find(ip) == string::npos) {
	line.insert(line.find(":allow"), "," + ip);
      }
      else {
	add_ip = false;
	break;
      }
    }
    file += line + "\n";
  }

  if (!file.empty()) {
    file.erase(file.size() - 1);
  }

  if (!found_sshd) {
    file += "sshd:" + ip + ":allow";
  }

  if (add_ip) {
    f.clear();
    f.seekp(0);
    f << file;
    cout << (ip + " added to hosts.allow") << endl;
    f.close();
    
    ofstream log("ssh.log", ios::app);
    time_t t;
    struct tm * timeinfo;
    time(&t);
    timeinfo = localtime(&t);
    char buf[100];
    strftime(buf,100,"%c: ", timeinfo);
    log << buf << ip << endl;
    log.close();
  }
  else {
    cout << (ip + " already in hosts.allow") << endl;
  }
  return 0;
}

