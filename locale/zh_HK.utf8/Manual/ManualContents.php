<?php
/* This table of contents allows the choice to display one section or select multiple sections to format for print.
     Selecting multiple sections is for printing
-->

<!-- The individual topics in the manual are in straight html files that are called along with the header and foot from here.
     No style, inline style or style sheet on purpose.
     In this way the help can be easily broken into sections for online context-sensitive help.
		 The only html used in them are:
		 <br />
		 <div>
		 <table>
		 <font>
		 <b>
		 <u>
		 <ul>
		 <ol>

		 Comments beginning with Help Begin and Help End denote the beginning and end of a section that goes into the online help.
		 What section is named after Help Begin: and there can be multiple sections separated with a comma.
-->';*/
// $PageSecurity=1;
$PathPrefix='../../';
//include($PathPrefix.'includes/session.php');

include('ManualHeader.html');
?>
	<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'); ?>" method="POST">
<?php
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (((!isset($_POST['Submit'])) AND (!isset($_GET['ViewTopic']))) OR
     ((isset($_POST['Submit'])) AND (isset($_POST['SelectTableOfContents'])))) {
// if not submittws then coming into manual to look at TOC
// if SelectTableOfContents set then user wants it displayed
?>
<?php
  if (!isset($_POST['Submit'])) {
?>
          <input type="submit" name="Submit" value="��ʾѡȡ">
					�����������Ӳ鿴�������ȡ��Ȼ�����ʾҪ��ӡ�ĸ�ʽ
					<br /><br /><br />
<?php
  }
?>
    <table cellpadding="0" cellspacing="0">
      <tr>
        <td>
<?php
  if (!isset($_POST['Submit'])) {
?>
  	      <input type="checkbox" name="SelectTableOfContents">
<?php
  }
?>
          <font size="+3"><b>����</b></font>
          <br /><br />
          <UL>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectIntroduction">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=Introduction'; ?>">���</A>
<?php
  } else {
?>
              <A href="#Introduction">���</A>
<?php
	}
?>
              <UL>
                <LI>ΪʲôҪѡ��һ�������?</LI>
              </UL>
              <br />
            </LI>
						<LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectRequirements">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=Requirements'; ?>">Ҫ��</A>
<?php
  } else {
?>
              <A href="#Requirements">Ҫ��</A>
<?php
	}
?>
              <UL>
                <LI>Ӳ��Ҫ��</LI>
                <LI>���Ҫ��</LI>
                <LI>��webERP��ά������</LI>
              </UL>
              <br />
            </LI>
						<LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectGettingStarted">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=GettingStarted'; ?>">��ʶwebERP</A>
<?php
  } else {
?>
              <A HREF="#GettingStarted">����</A>
<?php
  }
?>
              <UL>
                <LI>ǰ������</LI>
                <LI>����PHP�ű�</LI>
                <LI>�������ݿ�</LI>
                <LI>�༭config.php</LI>
                <LI>��һ�ε�¼</LI>
                <LI>Ƥ�����û�������</LI>
                <LI>�����û�</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectSecuritySchema">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=SecuritySchema'; ?>">��ȫ�ƻ�</A>
<?php
  } else {
?>
              <A HREF="#SecuritySchema">��ȫ�ƻ�</A>
<?php
  }
?>
            </LI>
            <br /><br />
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectCreatingNewSystem">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=CreatingNewSystem'; ?>">����һ����ϵͳ</A>
<?php
  } else {
?>
              <A HREF="#CreatingNewSystem">����һ����ϵͳ</A>
<?php
  }
?>
              <UL>
                <LI>������ʾ���ݿ�</LI>
                <LI>����ϵͳ</LI>
                <LI>���ÿ����Ʒ</LI>
                <LI>���������</LI>
                <LI>����������ϵ�����</LI>
                <LI>���ù˿�</LI>
                <LI>����˿����</LI>
                <LI>Ӧ���˿�˶�</LI>
                <LI>��β</LI>
              </UL>
              <br />
						</LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectSystemConventions">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=SystemConventions'; ?>">ϵͳ����</A>
<?php
  } else {
?>
              <A HREF="#SystemConventions">ϵͳ����</A>
<?php
  }
?>
              <UL>
                <LI>�˵�����</LI>
                <LI>����</LI>
              </UL>
              <br />
            </LI>
						<LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectInventory">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=Inventory'; ?>">��� (aka "���")</A>
<?php
  } else {
?>
              <A HREF="#Inventory">��� (aka "���")</A>
<?php
  }
?>
              <UL>
                <LI>����</LI>
                <LI>���ϵͳ����</LI>
                <LI>�������</LI>
                <LI>���ӿ����Ʒ</LI>
                <LI>��Ʒ����</LI>
                <LI>��Ʒ����</LI>
                <LI>����</LI>
                <LI>������λ</LI>
                <LI>���ö�������</LI>
                <LI>��װ���</LI>
                <LI>��װ����</LI>
                <LI>������λ</LI>
                <LI>��ǰ����̭</LI>
                <LI>������߹���</LI>
                <LI>������װ��Ʒ</LI>
                <LI>�ܿ�</LI>
                <LI>���л�</LI>
                <LI>����</LI>
                <LI>�ۿ�����</LI>
                <LI>С��λ��</LI>
                <LI>���ɱ�</LI>
                <LI>���ϳɱ�</LI>
                <LI>�����ɱ�</LI>
                <LI>�������</LI>
                <LI>��׼�ɱ�����</LI>
                <LI>ʵ�ʳɱ�</LI>
                <LI>��������ɱ������ϳɱ������������</LI>
                <LI>ѡ�������Ʒ</LI>
                <LI>�޸Ŀ����Ʒ</LI>
                <LI>�޸Ŀ������</LI>
                <LI>�޸�Ϊ������߹����ʶ</LI>
                <LI>�������</LI>
                <LI>����������</LI>
                <LI>�����������</LI>
                <LI>�ʲ���ծ���������˻�</LI>
                <LI>���������ʹ����˻�</LI>
                <LI>�ɹ��۸�����˻�</LI>
                <LI>�������������˻�</LI>
                <LI>��Դ����</LI>
                <LI>���ص�ά��</LI>
                <LI>������</LI>
                <LI>���ص�ת��</LI>
                <LI>��汨��Ͳ�ѯ</LI>
                <LI>���״̬��ѯ</LI>
                <LI>���仯��ѯ</LI>
                <LI>���������ѯ</LI>
                <LI>����ֵ����</LI>
                <LI>���ƻ�����</LI>
                <LI>����̵�</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectAccountsReceivable">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=AccountsReceivable'; ?>">Ӧ���˿�</A>
<?php
  } else {
?>
              <A HREF="#AccountsReceivable">Ӧ���˿�</A>
<?php
  }
?>
              <UL>
                <LI>����</LI>
                <LI>����</LI>
                <LI>�����¹˿�</LI>
                <LI>�˿ʹ���</LI>
                <LI>�˿�����</LI>
                <LI>��ַ�� 1, 2, 3 �� 4</LI>
                <LI>����</LI>
                <LI>��Ʊ�ۿ�</LI>
                <LI>�����ۿ�</LI>
                <LI>�˿ͽ�����ʼ��</LI>
                <LI>��������</LI>
                <LI>����״��������</LI>
                <LI>���ö��</LI>
                <LI>��Ʊ��ַ</LI>
                <LI>����˿ͷֹ�˾</LI>
                <LI>�ֹ�˾����</LI>
                <LI>�ֹ�˾����</LI>
                <LI>�ֹ�˾������/�绰/����/��ַ</LI>
                <LI>������Ա</LI>
                <LI>����ֿ�</LI>
                <LI>Forward Date From A Day In The Month</LI>
                <LI>��������</LI>
                <LI>�绰/����/����</LI>
                <LI>˰�յ���</LI>
                <LI>ֹͣ����</LI>
                <LI>Ĭ�����乫˾</LI>
                <LI>�ʼ���ַ1, 2, 3 �� 4</LI>
                <LI>�޸Ĺ˿�ϸ��</LI>
                <LI>������</LI>
              </UL>
              <br />
            </LI>
            <LI>

<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectAccountsPayable">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=AccountsPayable'; ?>">Ӧ���˿�</A>
<?php
  } else {
?>
              <A HREF="#AccountsPayable">Ӧ���˿�</A>
<?php
  }
?>
              <UL>
                <LI>����</LI>
                <LI>����</LI>
                <LI>�����¹�Ӧ��</LI>
                <LI>��Ӧ�̴���</LI>
                <LI>��Ӧ������</LI>
                <LI>��ַ��1��2��3��4</LI>
                <LI>��Ӧ�̽�����ʼ��</LI>
                <LI>��������</LI>
                <LI>Bank Particulars/Reference</LI>
                <LI>�����˻�����</LI>
                <LI>����</LI>
		<LI>���֪ͨ</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectSalesPeople">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=SalesPeople'; ?>">������Ա</A>
<?php
  } else {
?>
              <A HREF="#SalesPeople">������Ա</A>
<?php
  }
?>
              <UL>
                <LI>������Ա��¼</LI>
                <LI>������Ա����</LI>
                <LI>������Ա���ƣ��绰���ʹ���</LI>
                <LI>������ԱӶ���ʺ��ۿ۵�</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectCurrencies">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=Currencies'; ?>">����</A>
<?php
  } else {
?>
              <A HREF="#Currencies">����</A>
<?php
  }
?>
              <UL>
                <LI>������д</LI>
                <LI>��������</LI>
                <LI>���ҹ���</LI>
                <LI>���Ұٷֵ�λ����</LI>
                <LI>����</LI>
              </UL>
              <br />
            </LI>
            <LI>

<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectSalesTypes">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=SalesTypes'; ?>">�������� / �۸��</A>
<?php
  } else {
?>
              <A HREF="#SalesTypes">��������/�۸��</A>
<?php
  }
?>
              <UL>
                <LI>��������/ �۸��</LI>
                <LI>�������ʹ���</LI>
                <LI>������������</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectPaymentTerms">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=PaymentTerms'; ?>">��������</A>
<?php
  } else {
?>
              <A HREF="#PaymentTerms">��������</A>
<?php
  }
?>
              <UL>
                <LI>��������</LI>
                <LI>������������</LI>
                <LI>������������</LI>
                <LI>��������/�¸���ĳ�쵽��</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectCreditStatus">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=CreditStatus'; ?>">����״��</A>
<?php
  } else {
?>
              <A HREF="#CreditStatus">����״��</A>
<?php
  }
?>
              <UL>
                <LI>����״������</LI>
                <LI>����״������</LI>
                <LI>״������</LI>
                <LI>��ֹ����Ʊ</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectTax">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=Tax'; ?>">˰��</A>
<?php
  } else {
?>
              <A HREF="#Tax">˰��</A>
<?php
  }
?>
              <UL>
                <LI>˰�ռ���</LI>
                <LI>����</LI>
                <LI>����˰��</LI>
                <LI>һ��˰������ڵ���������--2��˰��ˮƽ:</LI>
                <LI>һ��˰����������۵�����--3��˰��ˮƽ:</LI>
                <LI>����˰����������۵�����--3��˰��ˮƽ:</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectPrices">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=Prices'; ?>">�۸���ۿ�</A>
<?php
  } else {
?>
              <A HREF="#Prices">�۸���ۿ�</A>
<?php
  }
?>
              <UL>
                <LI>�۸���ۿ�</LI>
                <LI>���۸���</LI>
                <LI>ά���۸�</LI>
                <LI>�ۿ۾���</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectARTransactions">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=ARTransactions'; ?>">Ӧ���˿��</A>
<?php
  } else {
?>
              <A HREF="#ARTransactions">Ӧ���˿��</A>
<?php
  }
?>
              <UL>
                <LI>Ϊ��������Ʊ</LI>
                <LI>ѡ��Ҫ����Ʊ�Ķ���</LI>
                <LI>��ѡ��Ķ������ɷ�Ʊ</LI>
                <LI>���ַ�Ʊ</LI>
                <LI>�տ�����</LI>
                <LI>�տ� - �˿�</LI>
                <LI>�տ� - ����</LI>
                <LI>�տ� - ���Һͻ���</LI>
                <LI>�տ� - ���ʽ</LI>
                <LI>�տ� - ���</LI>
                <LI>�տ� - �ۿ�</LI>
                <LI>�տ� - ��������Ʊ</LI>
                <LI>���ʲ���</LI>
                <LI>�տ��</LI>
                <LI>����б�</LI>
                <LI>������ֿ�����˿�</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectARInquiries">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=ARInquiries'; ?>">Ӧ���˿��ѯ</A>
<?php
  } else {
?>
              <A HREF="#ARInquiries">Ӧ���˿��ѯ</A>
<?php
  }
?>
              <UL>
                <LI>�˿Ͳ�ѯ</LI>
                <LI>�˿��˻���ѯ</LI>
                <LI>����ϸ�ڲ�ѯ</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectARReports">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=ARReports'; ?>">Ӧ���˿��</A>
<?php
  } else {
?>
              <A HREF="#ARReports">Ӧ���˿��</A>
<?php
  }
?>
              <UL>
                <LI>�˿� - ����</LI>
                <LI>���ڹ˿��˻���</LI>
                <LI>�˿Ͷ��˵�</LI>
                <LI>�˿ͽ����б�ѡ��</LI>
                <LI>��ӡ��Ʊ����ַ�Ʊ</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectSalesAnalysis">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=SalesAnalysis'; ?>">���۷���</A>
<?php
  } else {
?>
              <A HREF="#SalesAnalysis">���۷���</A>
<?php
  }
?>
              <UL>
                <LI>���۷���</LI>
                <LI>���۷��������ͷ</LI>
                <LI>���۷���������</LI>
                <LI>�Զ����۱���</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectSalesOrders">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=SalesOrders'; ?>">���۶���</A>
<?php
  } else {
?>
              <A HREF="#SalesOrders">���۶���</A>
<?php
  }
?>
              <UL>
                <LI>���۶���</LI>
                <LI>���۶�������</LI>
                <LI>�������۶���</LI>
                <LI>���۶��� - ѡ��˿ͺͷֹ�˾</LI>
                <LI>ѡ�񶩵�����Ʒ</LI>
                <LI>����ϸ��</LI>
                <LI>�޸Ķ���</LI>
				<LI>���۵�</LI>
				<LI>���ڶ���</LI>
				<LI>��̨���� - ֱ����������</LI>
				<LI>���ݲ�Ʒ����߹˿��飨���󣩹����ۿ�</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="PurchaseOrdering">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=PurchaseOrdering'; ?>">�ɹ�����</A>
<?php
  } else {
?>
              <A HREF="#Shipments">�ɹ�����</A>
<?php
  }
?>
              <UL>
                <LI>����</LI>
                <LI>�ɹ�����</LI>
                <LI>�����²ɹ�����</LI>
                <LI>�ɹ�������Ȩ </LI>
                <LI>�ɹ������ջ�</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectShipments">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=Shipments'; ?>">����</A>
<?php
  } else {
?>
              <A HREF="#Shipments">����</A>
<?php
  }
?>
              <UL>
                <LI>����</LI>
                <LI>�������ʹ���</LI>
                <LI>��������</LI>
                <LI>����ɱ�</LI>
                <LI>�ر�����</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectContractCosting">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=Contracts'; ?>">��ͬ�ɱ�</A>
<?php
  } else {
?>
              <A HREF="#Contracts">��ͬ�ɱ�</A>
<?php
  }
?>
              <UL>
                <LI>��ͬ�ɱ�����</LI>
                <LI>�����º�ͬ</LI>
                <LI>ѡ���ͬ</LI>
                <LI>��ͬ����</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectManufacturing">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=Manufacturing'; ?>">����</A>
<?php
  } else {
?>
              <A HREF="#Manufacturing">����</A>
<?php
  }
?>
              <UL>
                <LI>�������</LI>
                <LI>��������</LI>
                <LI>��������</LI>
                <LI>�����ջ�</LI>
                <LI>��������</LI>
                <LI>�رչ���</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectMRP">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=MRP'; ?>">
              ��������ƻ�</A>
<?php
  } else {
?>
              <A HREF="#MRP">��������ƻ�</A>
<?php
  }
?>
              <UL>
                <LI>MRP ����</LI>
                <LI>������������</LI>
                <LI>��������</LI>
                <LI>�����������ƻ�</LI>
                <LI>����MRP����</LI>
                <LI>����ԭ��</LI>
                <LI>MRP ����</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectGeneralLedger">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=GeneralLedger'; ?>">����</A>
<?php
  } else {
?>
              <A HREF="#GeneralLedger">����</A>
<?php
  }
?>
              <UL>
                <LI>���ʸ���</LI>
                <LI>��Ŀ��</LI>
                <LI>�����˻�</LI>
                <LI>�����˻�����</LI>
                <LI>������������</LI>
                <LI>���۷�¼</LI>
                <LI>����¼</LI>
                <LI>EDI</LI>
                <LI>EDI����</LI>
                <LI>���� EDI ��Ʊ</LI>
              </UL>
              <br />
            </LI>
            <LI>
 <?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectFixedAssets">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=FixedAssets'; ?>">�̶��ʲ�</A>
<?php
  } else {
?>
              <A HREF="#Fixed Assets">�̶��ʲ�</A>
<?php
  }
?>
              <UL>
                <LI>�̶��ʲ�����</LI>
                <LI>�����̶��ʲ�</LI>
                <LI>ѡ��̶��ʲ�</LI>
                <LI>�����۾�</LI>
                <LI>�̶��ʲ��ƻ�</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectReportBuilder">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=ReportBuilder'; ?>">�Զ���SQL���湤��</A>
<?php
  } else {
?>
              <A HREF="#ReportBuilder">���湤��</A>
<?php
  }
?>
              <UL>
                <LI>���湤�߽���</LI>
                <LI>�������</LI>
                <LI>���뵼������</LI>
                <LI>����ı༭ ���� ������</LI>
                <LI>����һ���±��� - ʶ��</LI>
                <LI>�����±��� - ҳ������</LI>
                <LI>�����±��� - ָ�����ݿ�������</LI>
                <LI>�����±��� - ָ����ѯ�ֶ�</LI>
                <LI>�����±��� - ����Ͱ�������</LI>
                <LI>�鿴����</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="PettyCash">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=PettyCash'; ?>">С���ֽ����ϵͳ</A>
<?php
  } else {
?>
              <A HREF="#PettyCash">С���ֽ����ϵͳ</A>
<?php
  }
?>
              <UL>
                <LI>����</LI>
                <LI>���û�������</LI>

              </UL>
              <br />
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectMultilanguage">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=Multilanguage'; ?>">������</A>
<?php
  } else {
?>
              <A HREF="#Multilanguage">������</A>
<?php
  }
?>
              <UL>
                <LI>�����Լ��</LI>
                <LI>�ؽ�ϵͳĬ�������ļ�</LI>
                <LI>Ϊϵͳ����������</LI>
                <LI>�༭�����ļ�ͷ</LI>
                <LI>�༭�����ļ�ģ��</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectSpecialUtilities">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=SpecialUtilities'; ?>">���⹤��</A>
<?php
  } else {
?>
              <A HREF="#SpecialUtilities">���⹤��</A>
<?php
  }
?>
              <UL>
                <LI>���±�׼�ɱ��������۷���</LI>
                <LI>�ı�˿ʹ���</LI>
                <LI>�ı������</LI>
                <LI>�������ص�</LI>
                <LI>���¹���ָ����������</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectNewScripts">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=NewScripts'; ?>">�з� - ����</A>
<?php
  } else {
?>
              <A HREF="#NewScripts">�з� - ����</A>
<?php
  }
?>
              <UL>
                <LI>·���ṹ</LI>
                <LI>session.php</LI>
                <LI>header.php</LI>
                <LI>footer.php</LI>
                <LI>config.php</LI>
                <LI>PDFStarter.php</LI>
                <LI>���ݿ����� - ConnectDB.inc</LI>
                <LI>DateFunctions.inc</LI>
                <LI>SQL_CommonFuctions.inc</LI>
              </UL>
              <br />
            </LI>
            <LI>





<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectAPI">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=API'; ?>">�з� - API</A>
<?php
  } else {
?>
              <A HREF="#API">�з� - API</A>
<?php
  }
?>
              <br />
              <br />
            </LI>
            <LI>






<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectStructure">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=Structure'; ?>">�з� - �ṹ</A>
<?php
  } else {
?>
              <A HREF="#Structure">�з� - �ṹ</A>
<?php
  }
?>
              <UL>
                <LI>���۶���</LI>
                <LI>����</LI>
                <LI>���˳ɱ�</LI>
                <LI>�������۶���</LI>
                <LI>����Ʊ</LI>
                <LI>Ӧ���˻�/�˿��˻�</LI>
                <LI>Ӧ���˻��տ�</LI>
                <LI>Ӧ���˻�����</LI>
                <LI>���۷���</LI>
                <LI>�ɹ�����</LI>
                <LI>���</LI>
                <LI>����ѯ</LI>
                <LI>Ӧ���˻�</LI>
                <LI>��Ӧ�̸���</LI>
              </UL>
              <br />
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectContributors">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic=Contributors'; ?>">������ -��л</A>
<?php
  } else {
?>
              <A HREF="#Contributors">������ - ��л</A>
<?php
  }
?>
            </LI>
          </UL>
        </td>
      </tr>
    </table>

<?php
}
?>
  </form>
<?php

if (!isset($_GET['ViewTopic'])) {
	$_GET['ViewTopic'] = '';
}

if ($_GET['ViewTopic'] == 'Introduction' OR isset($_POST['SelectIntroduction'])) {
  include('ManualIntroduction.html');
}

if ($_GET['ViewTopic'] == 'Requirements' OR isset($_POST['SelectRequirements'])) {
  include('ManualRequirements.html');
}

if ($_GET['ViewTopic'] == 'GettingStarted' OR isset($_POST['SelectGettingStarted'])) {
  include('ManualGettingStarted.html');
}

if ($_GET['ViewTopic'] == 'SecuritySchema' OR isset($_POST['SelectSecuritySchema'])) {
  include('ManualSecuritySchema.html');
}

if ($_GET['ViewTopic'] == 'CreatingNewSystem' OR isset($_POST['SelectCreatingNewSystem'])) {
  include('ManualCreatingNewSystem.html');
}

if ($_GET['ViewTopic'] == 'SystemConventions' OR isset($_POST['SelectSystemConventions'])) {
  include('ManualSystemConventions.html');
}

if ($_GET['ViewTopic'] == 'Inventory' OR isset($_POST['SelectInventory'])) {
  include('ManualInventory.html');
}

if ($_GET['ViewTopic'] == 'AccountsReceivable' OR isset($_POST['SelectAccountsReceivable'])) {
  include('ManualAccountsReceivable.html');
}

if ($_GET['ViewTopic'] == 'AccountsPayable' OR isset($_POST['SelectAccountsPayable'])) {
  include('ManualAccountsPayable.html');
}

if ($_GET['ViewTopic'] == 'SalesPeople' OR isset($_POST['SelectSalesPeople'])) {
  include('ManualSalesPeople.html');
}
if ($_GET['ViewTopic'] == 'Currencies' OR isset($_POST['Currencies'])) {
  include('ManualCurrencies.html');
}
if ($_GET['ViewTopic'] == 'SalesTypes' OR isset($_POST['SelectSalesTypes'])) {
  include('ManualSalesTypes.html');
}

if ($_GET['ViewTopic'] == 'PaymentTerms' OR isset($_POST['SelectPaymentTerms'])) {
  include('ManualPaymentTerms.html');
}

if ($_GET['ViewTopic'] == 'CreditStatus' OR isset($_POST['SelectCreditStatus'])) {
  include('ManualCreditStatus.html');
}

if ($_GET['ViewTopic'] == 'Tax' OR isset($_POST['SelectTax'])) {
  include('ManualTax.html');
}

if ($_GET['ViewTopic'] == 'Prices' OR isset($_POST['SelectPrices'])) {
  include('ManualPrices.html');
}

if ($_GET['ViewTopic'] == 'ARTransactions' OR isset($_POST['SelectARTransactions'])) {
  include('ManualARTransactions.html');
}

if ($_GET['ViewTopic'] == 'ARInquiries' OR isset($_POST['SelectARInquiries'])) {
  include('ManualARInquiries.html');
}

if ($_GET['ViewTopic'] == 'ARReports' OR isset($_POST['SelectARReports'])) {
  include('ManualARReports.html');
}

if ($_GET['ViewTopic'] == 'SalesAnalysis' OR isset($_POST['SelectSalesAnalysis'])) {
  include('ManualSalesAnalysis.html');
}

if ($_GET['ViewTopic'] == 'SalesOrders' OR isset($_POST['SelectSalesOrders'])) {
  include('ManualSalesOrders.html');
}

if ($_GET['ViewTopic'] == 'PurchaseOrdering' OR isset($_POST['PurchaseOrdering'])) {
  include('ManualPurchaseOrdering.html');
}
if ($_GET['ViewTopic'] == 'Shipments' OR isset($_POST['SelectShipments'])) {
  include('ManualShipments.html');
}
if ($_GET['ViewTopic'] == 'Contracts' OR isset($_POST['SelectContractCosting'])) {
  include('ManualContracts.html');
}
if ($_GET['ViewTopic'] == 'GeneralLedger' OR isset($_POST['SelectGeneralLedger'])) {
  include('ManualGeneralLedger.html');
}
if ($_GET['ViewTopic'] == 'FixedAssets' OR isset($_POST['SelectFixedAssets'])) {
  include('ManualFixedAssets.html');
}
if ($_GET['ViewTopic'] == 'Manufacturing' OR isset($_POST['SelectManufacturing'])) {
  include('ManualManufacturing.html');
}
if ($_GET['ViewTopic'] == 'MRP' OR isset($_POST['SelectMRP'])) {
  include('ManualMRP.html');
}
if ($_GET['ViewTopic'] == 'ReportBuilder' OR isset($_POST['SelectReportBuilder'])) {
  include('ManualReportBuilder.html');
}
if ($_GET['ViewTopic'] == 'PettyCash' OR isset($_POST['PettyCash'])) {
  include('ManualPettyCash.html');
}
if ($_GET['ViewTopic'] == 'Multilanguage' OR isset($_POST['SelectMultilanguage'])) {
  include('ManualMultilanguage.html');
}

if ($_GET['ViewTopic'] == 'SpecialUtilities' OR isset($_POST['SelectSpecialUtilities'])) {
  include('ManualSpecialUtilities.html');
}

if ($_GET['ViewTopic'] == 'NewScripts' OR isset($_POST['SelectNewScripts'])) {
  include('ManualNewScripts.html');
}

if ($_GET['ViewTopic'] == 'API' OR isset($_POST['SelectAPI'])) {
  include('ManualAPIFunctions.php');
}

if ($_GET['ViewTopic'] == 'Structure' OR isset($_POST['SelectStructure'])) {
  include('ManualDevelopmentStructure.html');
}

if ($_GET['ViewTopic'] == 'Contributors' OR isset($_POST['SelectContributors'])) {
  include('ManualContributors.html');
}

include('ManualFooter.html');
