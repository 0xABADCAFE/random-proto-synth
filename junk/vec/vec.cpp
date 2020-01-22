#include <cstdio>
#include <sys/time.h>
#include <time.h>

#ifdef __LP64__
    // 64-bit typedefs
    typedef signed char int8;
    typedef signed short int int16;
    typedef signed int int32;
    typedef signed long int int64;
    typedef unsigned char uint8;
    typedef unsigned short int uint16;
    typedef unsigned int uint32;
    typedef unsigned long int uint64;
    typedef float float32;
    typedef double float64;

    // Formatting Templates
    #define FS16 "hd"
    #define FU16 "hu"
    #define FS32 "d"
    #define FU32 "u"
    #define FS64 "ld"
    #define FU64 "lu"

#else
    // 32-bit typedefs
    typedef signed char int8;
    typedef signed short int int16;
    typedef signed long int int32;
    typedef signed long long int int64;
    typedef unsigned char uint8;
    typedef unsigned short int uint16;
    typedef unsigned long int uint32;
    typedef unsigned long long int uint64;
    typedef float float32;
    typedef double float64;

    // Formatting Templates
    #define FS16 "hd"
    #define FU16 "hu"
    #define FS32 "ld"
    #define FU32 "lu"
    #define FS64 "lld"
    #define FU64 "llu"

#endif


/**
 * NanoTime
 *
 * Nanosecond precision timing.
 */
class NanoTime {
    public:
        typedef uint64 Value;
        static Value mark() {
            timespec current;
            clock_gettime(CLOCK_MONOTONIC, &current);
            Value  mark = 1000000000ULL * current.tv_sec ;
            return mark + current.tv_nsec;
        }
};

template<int N> class Packet {

    typedef float32 v4f __attribute__ ((vector_size (16)));

    private:
        v4f data[N/4];

    public:
        Packet(const float v) {
            fill(v);
        }

        void fill(const float32 v) {
            const v4f vec = { v, v, v, v };
            for (unsigned i=0; i < sizeof(data)/sizeof(v4f); ++i) {
                data[i] = vec;
            }
        }

        void bias(const float32 v) {
            for (unsigned i=0; i < sizeof(data)/sizeof(v4f); ++i) {
                data[i] += v;
            }
        }

        void scale(const float32 v) {
            for (unsigned i=0; i < sizeof(data)/sizeof(v4f); ++i) {
                data[i] *= v;
            }
        }

        void modulate(const Packet& p) {
            for (unsigned i=0; i < sizeof(data)/sizeof(v4f); ++i) {
                data[i] *= p.data[i];
            }
        }

        void modulate(const Packet* p) {
            for (unsigned i=0; i < sizeof(data)/sizeof(v4f); ++i) {
                data[i] *= p->data[i];
            }
        }

        void accumulate(const Packet& p, const float32 scale) {
            for (unsigned i=0; i < sizeof(data)/sizeof(v4f); ++i) {
                data[i] += (p.data[i] * scale);
            }
        }

        void accumulate(const Packet* p, const float32 scale) {
            for (unsigned i=0; i < sizeof(data)/sizeof(v4f); ++i) {
                data[i] += (p->data[i] * scale);
            }
        }

        void dump() const {
            for (unsigned i=0; i < sizeof(data)/sizeof(v4f); ++i) {
                std::printf(
                    "\t%0.6f %0.6f %0.6f %0.6f\n",
                    data[i][0],
                    data[i][1],
                    data[i][2],
                    data[i][3]
                );
            }
        }
};

typedef Packet<32> SignalPacket;


int main() {

    SignalPacket p(1.0), q(2.0);

    int i = 100000000;
    NanoTime::Value start = NanoTime::mark();
    while (i--) {
        p.accumulate(q, 0.01);
    }
    NanoTime::Value end = NanoTime::mark();

    p.dump();

    std::printf("Took %" FU64 " ns\n", end - start);

    return 0;
}
