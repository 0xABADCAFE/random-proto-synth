#!/bin/sh
g++ -march=native -Wall -W -Ofast -ftree-vectorize vec.cpp -o vec
g++ -march=native -S -Wall -W -Ofast -ftree-vectorize vec.cpp -o vec.asm
g++ -m32 -mfpmath=387 -Wall -W -Ofast -fno-tree-vectorize vec.cpp -o novec
g++ -m32 -mfpmath=387 -S -Wall -W -Ofast -fno-tree-vectorize vec.cpp -o novec.asm
