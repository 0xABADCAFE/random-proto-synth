	.file	"vec.cpp"
	.section	.rodata.str1.1,"aMS",@progbits,1
.LC2:
	.string	"\t%0.6f %0.6f %0.6f %0.6f\n"
.LC3:
	.string	"Took %llu ns\n"
	.section	.text.unlikely,"ax",@progbits
.LCOLDB5:
	.section	.text.startup,"ax",@progbits
.LHOTB5:
	.p2align 4,,15
	.globl	main
	.type	main, @function
main:
.LFB40:
	.cfi_startproc
	leal	4(%esp), %ecx
	.cfi_def_cfa 1, 0
	andl	$-16, %esp
	pushl	-4(%ecx)
	pushl	%ebp
	.cfi_escape 0x10,0x5,0x2,0x75,0
	movl	%esp, %ebp
	pushl	%edi
	pushl	%esi
	pushl	%ebx
	pushl	%ecx
	.cfi_escape 0xf,0x3,0x75,0x70,0x6
	.cfi_escape 0x10,0x7,0x2,0x75,0x7c
	.cfi_escape 0x10,0x6,0x2,0x75,0x78
	.cfi_escape 0x10,0x3,0x2,0x75,0x74
	movl	$1000000000, %edi
	subl	$464, %esp
	movl	%gs:20, %eax
	movl	%eax, -28(%ebp)
	xorl	%eax, %eax
	leal	-304(%ebp), %eax
	pushl	%eax
	pushl	$1
	call	clock_gettime
	movl	%edi, %eax
	movl	$100000000, -336(%ebp)
	movl	$0x3f800000, -296(%ebp)
	imull	-304(%ebp)
	movl	$0x3f800000, -292(%ebp)
	movl	$0x3f800000, -288(%ebp)
	addl	$16, %esp
	movl	$0x3f800000, -284(%ebp)
	movl	$0x3f800000, -184(%ebp)
	movl	$0x3f800000, -180(%ebp)
	movl	$0x3f800000, -176(%ebp)
	movl	$0x3f800000, -172(%ebp)
	movl	$0x3f800000, -200(%ebp)
	movl	$0x3f800000, -196(%ebp)
	movl	$0x3f800000, -192(%ebp)
	movl	%eax, -456(%ebp)
	movl	-300(%ebp), %eax
	movl	%edx, -452(%ebp)
	movl	$0x3f800000, -188(%ebp)
	movl	$0x3f800000, -216(%ebp)
	movl	$0x3f800000, -212(%ebp)
	movl	%eax, -464(%ebp)
	sarl	$31, %eax
	movl	$0x3f800000, -208(%ebp)
	movl	%eax, -460(%ebp)
	movl	$0x3f800000, -204(%ebp)
	movl	$0x3f800000, -232(%ebp)
	movl	$0x3f800000, -228(%ebp)
	movl	$0x3f800000, -224(%ebp)
	movl	$0x3f800000, -220(%ebp)
	movl	$0x3f800000, -248(%ebp)
	movl	$0x3f800000, -244(%ebp)
	movl	$0x3f800000, -240(%ebp)
	movl	$0x3f800000, -236(%ebp)
	movl	$0x3f800000, -264(%ebp)
	movl	$0x3f800000, -260(%ebp)
	flds	.LC1
	movl	$0x3f800000, -256(%ebp)
	movl	$0x3f800000, -252(%ebp)
	movl	$0x3f800000, -280(%ebp)
	movl	$0x3f800000, -276(%ebp)
	movl	$0x3f800000, -272(%ebp)
	movl	$0x3f800000, -268(%ebp)
	.p2align 4,,10
	.p2align 3
.L2:
	flds	-280(%ebp)
	fadd	%st(1), %st
	fstps	-328(%ebp)
	movl	-328(%ebp), %eax
	flds	-276(%ebp)
	movl	%eax, -344(%ebp)
	movl	%eax, -280(%ebp)
	fadd	%st(1), %st
	fstps	-324(%ebp)
	movl	-324(%ebp), %eax
	flds	-272(%ebp)
	movl	%eax, -348(%ebp)
	movl	%eax, -276(%ebp)
	fadd	%st(1), %st
	fstps	-320(%ebp)
	movl	-320(%ebp), %eax
	flds	-268(%ebp)
	movl	%eax, -352(%ebp)
	movl	%eax, -272(%ebp)
	fadd	%st(1), %st
	fstps	-316(%ebp)
	movl	-316(%ebp), %eax
	flds	-264(%ebp)
	movl	%eax, -356(%ebp)
	movl	%eax, -268(%ebp)
	fadd	%st(1), %st
	fstps	-328(%ebp)
	movl	-328(%ebp), %eax
	flds	-260(%ebp)
	movl	%eax, -360(%ebp)
	movl	%eax, -264(%ebp)
	fadd	%st(1), %st
	fstps	-324(%ebp)
	movl	-324(%ebp), %eax
	flds	-256(%ebp)
	fadd	%st(1), %st
	fstps	-320(%ebp)
	flds	-252(%ebp)
	fadd	%st(1), %st
	fstps	-316(%ebp)
	movl	%eax, -364(%ebp)
	movl	%eax, -260(%ebp)
	movl	-320(%ebp), %eax
	flds	-248(%ebp)
	fadd	%st(1), %st
	movl	%eax, -368(%ebp)
	movl	%eax, -256(%ebp)
	movl	-316(%ebp), %eax
	fstps	-328(%ebp)
	movl	%eax, -372(%ebp)
	movl	%eax, -252(%ebp)
	movl	-328(%ebp), %eax
	flds	-244(%ebp)
	fadd	%st(1), %st
	movl	%eax, -376(%ebp)
	movl	%eax, -248(%ebp)
	fstps	-324(%ebp)
	movl	-324(%ebp), %eax
	flds	-240(%ebp)
	movl	%eax, -380(%ebp)
	movl	%eax, -244(%ebp)
	fadd	%st(1), %st
	fstps	-320(%ebp)
	movl	-320(%ebp), %eax
	flds	-236(%ebp)
	movl	%eax, -384(%ebp)
	movl	%eax, -240(%ebp)
	fadd	%st(1), %st
	fstps	-316(%ebp)
	movl	-316(%ebp), %eax
	flds	-232(%ebp)
	movl	%eax, -388(%ebp)
	movl	%eax, -236(%ebp)
	fadd	%st(1), %st
	fstps	-328(%ebp)
	flds	-228(%ebp)
	fadd	%st(1), %st
	fstps	-324(%ebp)
	flds	-224(%ebp)
	fadd	%st(1), %st
	fstps	-320(%ebp)
	movl	-328(%ebp), %eax
	flds	-220(%ebp)
	movl	%eax, -392(%ebp)
	movl	%eax, -232(%ebp)
	fadd	%st(1), %st
	movl	-324(%ebp), %eax
	movl	%eax, -396(%ebp)
	movl	%eax, -228(%ebp)
	fstps	-316(%ebp)
	movl	-320(%ebp), %eax
	flds	-216(%ebp)
	movl	%eax, -400(%ebp)
	movl	%eax, -224(%ebp)
	fadd	%st(1), %st
	movl	-316(%ebp), %eax
	movl	%eax, -404(%ebp)
	movl	%eax, -220(%ebp)
	fstps	-328(%ebp)
	movl	-328(%ebp), %eax
	flds	-212(%ebp)
	movl	%eax, -408(%ebp)
	movl	%eax, -216(%ebp)
	fadd	%st(1), %st
	fstps	-324(%ebp)
	movl	-324(%ebp), %eax
	flds	-208(%ebp)
	movl	%eax, -412(%ebp)
	movl	%eax, -212(%ebp)
	fadd	%st(1), %st
	fstps	-320(%ebp)
	movl	-320(%ebp), %eax
	flds	-204(%ebp)
	movl	%eax, -416(%ebp)
	movl	%eax, -208(%ebp)
	fadd	%st(1), %st
	fstps	-316(%ebp)
	movl	-316(%ebp), %eax
	movl	%eax, -420(%ebp)
	flds	-200(%ebp)
	movl	%eax, -204(%ebp)
	fadd	%st(1), %st
	fstps	-328(%ebp)
	movl	-328(%ebp), %eax
	flds	-196(%ebp)
	movl	%eax, -424(%ebp)
	movl	%eax, -200(%ebp)
	fadd	%st(1), %st
	fstps	-324(%ebp)
	movl	-324(%ebp), %eax
	flds	-192(%ebp)
	movl	%eax, -428(%ebp)
	movl	%eax, -196(%ebp)
	fadd	%st(1), %st
	fstps	-320(%ebp)
	movl	-320(%ebp), %eax
	flds	-188(%ebp)
	movl	%eax, -432(%ebp)
	movl	%eax, -192(%ebp)
	fadd	%st(1), %st
	fstps	-316(%ebp)
	movl	-316(%ebp), %eax
	flds	-184(%ebp)
	movl	%eax, -436(%ebp)
	movl	%eax, -188(%ebp)
	fadd	%st(1), %st
	fstps	-328(%ebp)
	movl	-328(%ebp), %eax
	flds	-180(%ebp)
	movl	%eax, -440(%ebp)
	movl	%eax, -184(%ebp)
	fadd	%st(1), %st
	fstps	-324(%ebp)
	flds	-176(%ebp)
	fadd	%st(1), %st
	fstps	-320(%ebp)
	flds	-172(%ebp)
	fadd	%st(1), %st
	fstps	-316(%ebp)
	movl	-324(%ebp), %eax
	movl	-320(%ebp), %edi
	subl	$1, -336(%ebp)
	movl	-316(%ebp), %esi
	flds	-296(%ebp)
	movl	%eax, -444(%ebp)
	movl	%eax, -180(%ebp)
	fadd	%st(1), %st
	movl	%edi, -176(%ebp)
	movl	%esi, -172(%ebp)
	fstps	-328(%ebp)
	movl	-328(%ebp), %ebx
	flds	-292(%ebp)
	movl	%ebx, -296(%ebp)
	fadd	%st(1), %st
	fstps	-324(%ebp)
	movl	-324(%ebp), %ecx
	flds	-288(%ebp)
	movl	%ecx, -292(%ebp)
	fadd	%st(1), %st
	fstps	-320(%ebp)
	movl	-320(%ebp), %edx
	flds	-284(%ebp)
	movl	%edx, -288(%ebp)
	fadd	%st(1), %st
	fstps	-316(%ebp)
	movl	-316(%ebp), %eax
	movl	%eax, -284(%ebp)
	jne	.L2
	fstp	%st(0)
	movl	%eax, -336(%ebp)
	movl	-344(%ebp), %eax
	subl	$8, %esp
	movl	%eax, -168(%ebp)
	movl	-348(%ebp), %eax
	movl	%eax, -164(%ebp)
	movl	-352(%ebp), %eax
	movl	%eax, -160(%ebp)
	movl	-356(%ebp), %eax
	movl	%eax, -156(%ebp)
	movl	-360(%ebp), %eax
	movl	%eax, -152(%ebp)
	movl	-364(%ebp), %eax
	movl	%eax, -148(%ebp)
	movl	-368(%ebp), %eax
	movl	%eax, -144(%ebp)
	movl	-372(%ebp), %eax
	movl	%eax, -140(%ebp)
	movl	-376(%ebp), %eax
	movl	%eax, -136(%ebp)
	movl	-380(%ebp), %eax
	movl	%eax, -132(%ebp)
	movl	-384(%ebp), %eax
	movl	%eax, -128(%ebp)
	movl	-388(%ebp), %eax
	movl	%eax, -124(%ebp)
	movl	-392(%ebp), %eax
	movl	%eax, -120(%ebp)
	movl	-396(%ebp), %eax
	movl	%eax, -116(%ebp)
	movl	-400(%ebp), %eax
	movl	%eax, -112(%ebp)
	movl	-404(%ebp), %eax
	movl	%eax, -108(%ebp)
	movl	-408(%ebp), %eax
	movl	%esi, -60(%ebp)
	movl	%ebx, -56(%ebp)
	leal	-28(%ebp), %ebx
	movl	%edx, -48(%ebp)
	movl	%edi, -64(%ebp)
	movl	%eax, -104(%ebp)
	movl	-412(%ebp), %eax
	movl	%ecx, -52(%ebp)
	movl	%eax, -100(%ebp)
	movl	-416(%ebp), %eax
	movl	%eax, -96(%ebp)
	movl	-420(%ebp), %eax
	movl	%eax, -92(%ebp)
	movl	-424(%ebp), %eax
	movl	%eax, -88(%ebp)
	movl	-428(%ebp), %eax
	movl	%eax, -84(%ebp)
	movl	-432(%ebp), %eax
	movl	%eax, -80(%ebp)
	movl	-436(%ebp), %eax
	movl	%eax, -76(%ebp)
	movl	-440(%ebp), %eax
	movl	%eax, -72(%ebp)
	movl	-444(%ebp), %eax
	movl	%eax, -68(%ebp)
	movl	-336(%ebp), %eax
	movl	%eax, -44(%ebp)
	leal	-304(%ebp), %eax
	pushl	%eax
	pushl	$1
	call	clock_gettime
	movl	-300(%ebp), %esi
	movl	$1000000000, %eax
	imull	-304(%ebp)
	movl	%esi, -344(%ebp)
	sarl	$31, %esi
	subl	-464(%ebp), %eax
	movl	%esi, -340(%ebp)
	leal	-156(%ebp), %esi
	sbbl	-460(%ebp), %edx
	addl	$16, %esp
	movl	%eax, -336(%ebp)
	movl	%edx, -332(%ebp)
	.p2align 4,,10
	.p2align 3
.L3:
	flds	(%esi)
	subl	$40, %esp
	addl	$16, %esi
	fstpl	24(%esp)
	flds	-20(%esi)
	fstpl	16(%esp)
	flds	-24(%esi)
	fstpl	8(%esp)
	flds	-28(%esi)
	fstpl	(%esp)
	pushl	$.LC2
	pushl	$1
	call	__printf_chk
	addl	$48, %esp
	cmpl	%ebx, %esi
	jne	.L3
	movl	-336(%ebp), %esi
	addl	-344(%ebp), %esi
	movl	-332(%ebp), %edi
	adcl	-340(%ebp), %edi
	subl	-456(%ebp), %esi
	sbbl	-452(%ebp), %edi
	pushl	%edi
	pushl	%esi
	pushl	$.LC3
	pushl	$1
	call	__printf_chk
	addl	$16, %esp
	xorl	%eax, %eax
	movl	-28(%ebp), %ecx
	xorl	%gs:20, %ecx
	jne	.L9
	leal	-16(%ebp), %esp
	popl	%ecx
	.cfi_remember_state
	.cfi_restore 1
	.cfi_def_cfa 1, 0
	popl	%ebx
	.cfi_restore 3
	popl	%esi
	.cfi_restore 6
	popl	%edi
	.cfi_restore 7
	popl	%ebp
	.cfi_restore 5
	leal	-4(%ecx), %esp
	.cfi_def_cfa 4, 4
	ret
.L9:
	.cfi_restore_state
	call	__stack_chk_fail
	.cfi_endproc
.LFE40:
	.size	main, .-main
	.section	.text.unlikely
.LCOLDE5:
	.section	.text.startup
.LHOTE5:
	.section	.rodata.cst4,"aM",@progbits,4
	.align 4
.LC1:
	.long	1017370378
	.ident	"GCC: (Ubuntu 5.4.0-6ubuntu1~16.04.12) 5.4.0 20160609"
	.section	.note.GNU-stack,"",@progbits
