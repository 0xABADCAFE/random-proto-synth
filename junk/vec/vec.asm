	.file	"vec.cpp"
	.section	.rodata.str1.1,"aMS",@progbits,1
.LC2:
	.string	"\t%0.6f %0.6f %0.6f %0.6f\n"
.LC3:
	.string	"Took %lu ns\n"
	.section	.text.unlikely,"ax",@progbits
.LCOLDB4:
	.section	.text.startup,"ax",@progbits
.LHOTB4:
	.p2align 4,,15
	.globl	main
	.type	main, @function
main:
.LFB40:
	.cfi_startproc
	pushq	%r14
	.cfi_def_cfa_offset 16
	.cfi_offset 14, -16
	pushq	%r13
	.cfi_def_cfa_offset 24
	.cfi_offset 13, -24
	movl	$1, %edi
	pushq	%r12
	.cfi_def_cfa_offset 32
	.cfi_offset 12, -32
	pushq	%rbp
	.cfi_def_cfa_offset 40
	.cfi_offset 6, -40
	pushq	%rbx
	.cfi_def_cfa_offset 48
	.cfi_offset 3, -48
	subq	$160, %rsp
	.cfi_def_cfa_offset 208
	movq	%rsp, %rsi
	movq	%fs:40, %rax
	movq	%rax, 152(%rsp)
	xorl	%eax, %eax
	call	clock_gettime
	imulq	$1000000000, (%rsp), %r12
	vmovaps	.LC0(%rip), %xmm1
	movl	$100000000, %eax
	movq	8(%rsp), %rbx
	vmovaps	%xmm1, %xmm2
	vmovaps	%xmm1, %xmm3
	vmovaps	%xmm1, %xmm4
	vmovaps	%xmm1, %xmm5
	vmovaps	%xmm1, %xmm6
	vmovaps	%xmm1, %xmm7
	vmovaps	%xmm1, %xmm8
	vmovaps	.LC1(%rip), %xmm0
	.p2align 4,,10
	.p2align 3
.L2:
	subl	$1, %eax
	vaddps	%xmm0, %xmm8, %xmm8
	vaddps	%xmm0, %xmm7, %xmm7
	vaddps	%xmm0, %xmm6, %xmm6
	vaddps	%xmm0, %xmm5, %xmm5
	vaddps	%xmm0, %xmm4, %xmm4
	vaddps	%xmm0, %xmm3, %xmm3
	vaddps	%xmm0, %xmm2, %xmm2
	vaddps	%xmm0, %xmm1, %xmm1
	jne	.L2
	movq	%rsp, %rsi
	movl	$1, %edi
	vmovaps	%xmm8, 16(%rsp)
	vmovaps	%xmm7, 32(%rsp)
	vmovaps	%xmm6, 48(%rsp)
	vmovaps	%xmm5, 64(%rsp)
	vmovaps	%xmm4, 80(%rsp)
	vmovaps	%xmm3, 96(%rsp)
	vmovaps	%xmm2, 112(%rsp)
	vmovaps	%xmm1, 128(%rsp)
	call	clock_gettime
	imulq	$1000000000, (%rsp), %rax
	movq	8(%rsp), %r13
	subq	%rbx, %rax
	leaq	28(%rsp), %rbx
	movq	%rax, %r14
	leaq	16(%rsp), %rax
	leaq	140(%rax), %rbp
	.p2align 4,,10
	.p2align 3
.L3:
	vxorpd	%xmm0, %xmm0, %xmm0
	movl	$.LC2, %esi
	movl	$1, %edi
	vxorpd	%xmm3, %xmm3, %xmm3
	movl	$4, %eax
	vxorpd	%xmm2, %xmm2, %xmm2
	addq	$16, %rbx
	vxorpd	%xmm1, %xmm1, %xmm1
	vcvtss2sd	-28(%rbx), %xmm0, %xmm0
	vcvtss2sd	-16(%rbx), %xmm3, %xmm3
	vcvtss2sd	-20(%rbx), %xmm2, %xmm2
	vcvtss2sd	-24(%rbx), %xmm1, %xmm1
	call	__printf_chk
	cmpq	%rbp, %rbx
	jne	.L3
	leaq	0(%r13,%r14), %rdx
	xorl	%eax, %eax
	movl	$.LC3, %esi
	movl	$1, %edi
	subq	%r12, %rdx
	call	__printf_chk
	xorl	%eax, %eax
	movq	152(%rsp), %rcx
	xorq	%fs:40, %rcx
	jne	.L10
	addq	$160, %rsp
	.cfi_remember_state
	.cfi_def_cfa_offset 48
	popq	%rbx
	.cfi_def_cfa_offset 40
	popq	%rbp
	.cfi_def_cfa_offset 32
	popq	%r12
	.cfi_def_cfa_offset 24
	popq	%r13
	.cfi_def_cfa_offset 16
	popq	%r14
	.cfi_def_cfa_offset 8
	ret
.L10:
	.cfi_restore_state
	call	__stack_chk_fail
	.cfi_endproc
.LFE40:
	.size	main, .-main
	.section	.text.unlikely
.LCOLDE4:
	.section	.text.startup
.LHOTE4:
	.section	.rodata.cst16,"aM",@progbits,16
	.align 16
.LC0:
	.long	1065353216
	.long	1065353216
	.long	1065353216
	.long	1065353216
	.align 16
.LC1:
	.long	1017370378
	.long	1017370378
	.long	1017370378
	.long	1017370378
	.ident	"GCC: (Ubuntu 5.4.0-6ubuntu1~16.04.12) 5.4.0 20160609"
	.section	.note.GNU-stack,"",@progbits
