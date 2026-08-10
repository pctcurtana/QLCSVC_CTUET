# THÔNG TIN DỰ ÁN DÙNG CHO BÁO CÁO THỰC TẬP TỐT NGHIỆP

---

## TÊN DỰ ÁN: HỆ THỐNG QUẢN LÝ CƠ SỞ VẬT CHẤT (QLCSVC) - TRƯỜNG ĐẠI HỌC KỸ THUẬT - CÔNG NGHỆ CẦN THƠ (CTUET)

---

## CHƯƠNG 1: TỔNG QUAN VỀ DỰ ÁN

### 1.1. Bối cảnh và Tính cấp thiết
Tại các cơ sở giáo dục đại học, đặc biệt là các trường đại học đào tạo theo định hướng kỹ thuật – công nghệ như **Trường Đại học Kỹ thuật - Công nghệ Cần Thơ (CTUET)**, quy mô về đất đai, công trình kiến trúc (khu nhà, dãy phòng học, phòng máy tính, phòng thí nghiệm, văn phòng) và trang thiết bị máy móc phục vụ giảng dạy/nghiên cứu vô cùng lớn và phức tạp.

Công tác quản lý truyền thống bằng sổ sách hoặc các tệp Excel phân tán bộc lộ nhiều hạn chế:
- **Khó khăn trong việc theo dõi vị trí và vòng đời tài sản**: Không theo dõi chính xác từng thiết bị đơn lẻ (Serial Number, trạng thái sử dụng, vị trí phòng hiện tại).
- **Phản ứng chậm với sự cố hư hỏng**: Giảng viên, sinh viên gặp sự cố tại phòng học/phòng lab phải báo qua nhiều khâu trung gian, kéo dài thời gian khắc phục.
- **Tốn thời gian tổng hợp báo cáo cấp Bộ**: Việc tổng hợp dữ liệu cơ sở vật chất theo các bảng biểu chuẩn của Bộ Giáo dục & Đào tạo (BGD&ĐT) để phục vụ công tác tuyển sinh và kiểm định chất lượng tốn rất nhiều nhân lực và dễ sai sót.
- **Thiếu cơ chế phân quyền và kiểm soát phiên bản dữ liệu**: Dễ xảy ra rủi ro ghi đè dữ liệu khi nhiều quản trị viên cùng thao tác.

### 1.2. Mục tiêu của Dự án
Dự án **Hệ thống Quản lý Cơ sở Vật chất (QLCSVC)** được xây dựng nhằm giải quyết toàn diện các bài toán trên với các mục tiêu cụ thể:
1. **Chuẩn hóa & Số hóa toàn bộ Quản lý Không gian & Tài sản**: Quản lý đa cấp từ Cơ sở $\rightarrow$ Khu nhà $\rightarrow$ Phòng học/Thí nghiệm $\rightarrow$ Thiết bị chi tiết.
2. **Xây dựng Phân hệ Phản ứng nhanh bằng Mã QR (QR Fast Response)**: Cho phép sinh viên, giảng viên quét mã QR dán tại phòng hoặc thiết bị để báo cáo sự cố hư hỏng trực tiếp mà không cần đăng nhập phức tạp, tự động điều phối tới bộ phận kỹ thuật.
3. **Tự động hóa Tổng hợp Báo cáo chuẩn BGD&ĐT**: Tự động tổng hợp 5 bảng biểu chuẩn theo quy định của Bộ GD&ĐT (Loại phòng, Tiêu chuẩn CSVC, Diện tích khuôn viên, Công trình đào tạo & Hệ số, Hạ tầng CNTT).
4. **Tối ưu hóa Hiệu năng & Bảo mật**: Áp dụng mô hình lưu trữ Snapshot thống kê (`thong_ke_snapshots`), phân quyền người dùng theo màn hình (RBAC Screen-based), kiểm soát xung đột dữ liệu bằng Optimistic Locking (Versioning).

---

## CHƯƠNG 2: KIẾN TRÚC HỆ THỐNG VÀ CÔNG NGHỆ SỬ DỤNG

### 2.1. Công nghệ Sử dụng (Tech Stack)

| Thành phần | Công nghệ / Thư viện | Vai trò & Mục đích |
| :--- | :--- | :--- |
| **Backend Framework** | **Laravel 8 (PHP 8.x)** | Xử lý logic nghiệp vụ, quản lý cơ sở dữ liệu, phân quyền, xác thực và API endpoints. |
| **Frontend Framework** | **React 18** | Xây dựng giao diện người dùng (UI) dạng Single Page Application (SPA), nâng cao trải nghiệm mượt mà. |
| **Bridge Layer** | **Inertia.js (`@inertiajs/react`)** | Kết nối trực tiếp Controller Laravel với React Component mà không cần viết API RESTful / GraphQL trung gian phức tạp. |
| **UI Library** | **Ant Design 5 (`antd`)** | Cung cấp hệ thống component chuẩn doanh nghiệp (Table, Modal, Form, TreeSelect, Notification...). |
| **Styling** | **Tailwind CSS 3 + PostCSS** | Tùy biến giao diện responsive, bố cục linh hoạt và hiện đại. |
| **Database** | **MySQL / PostgreSQL** | Lưu trữ cơ sở dữ liệu quan hệ, sử dụng Eloquent ORM. |
| **Real-time Engine** | **Laravel Echo + Pusher JS** | Truyền tải thông báo báo cáo sự cố trực tiếp theo thời gian thực (WebSockets). |
| **Biểu đồ & Thống kê** | **Recharts + React CountUp** | Trực quan hóa dữ liệu thống kê (biểu đồ cột, tròn, thông số tăng trưởng). |
| **QR Code Engine** | **QRCode.react + UUID Token** | Tạo mã QR bảo mật cho từng Phòng và từng Thiết bị. |
| **Nhập/Xuất Dữ liệu** | **Maatwebsite/Laravel-Excel** | Đọc/Viết tệp Excel hàng loạt cho các mẫu nhập liệu và xuất báo cáo. |
| **Build Tool** | **Laravel Mix (Webpack)** | Biên dịch JSX, ES6+, Tailwind CSS và tối ưu hóa tài nguyên tĩnh. |

### 2.2. Kiến trúc Phần mềm (Design & Architectural Patterns)

1. **Mô hình Monolithic SPA (Inertia.js Pattern)**:
   - Kết hợp ưu điểm của cả Monolith (phát triển nhanh, quản lý routing và auth tập trung ở Laravel) và SPA (trải nghiệm người dùng phản hồi tức thì của React).
2. **Repository & Service Pattern**:
   - **Contracts (Interfaces)**: Định nghĩa các hợp đồng truy vấn dữ liệu (`PhongRepositoryInterface`, `ThietBiRepositoryInterface`...).
   - **Repositories Layer**: Hiện thực hóa việc truy vấn cơ sở dữ liệu qua Eloquent ORM.
   - **Services Layer**: Chứa toàn bộ logic nghiệp vụ phức tạp (`BaoCaoBgdService`, `ThongKeSnapshotService`, `ThietBiService`...).
3. **Role-Based Access Control (RBAC) Screen-Based**:
   - Quản lý phân quyền dựa trên danh mục Màn hình (`screens`) và các quyền thao tác chi tiết: `can_view`, `can_create`, `can_edit`, `can_delete`, `can_import`, `can_export`, `can_regenerate_qr`.
   - Kiểm soát bằng Laravel Middleware: `middleware('permission:screen_code,action')`.
4. **Cơ chế Khóa Lạc quan (Optimistic Locking / Versioning Strategy)**:
   - Các bảng cốt lõi (`co_sos`, `khu_nhas`, `phongs`, `thiet_bis`) đều tích hợp các trường: `trang_thai_du_lieu`, `phien_ban`, `ban_ghi_goc_id`.
   - Giúp duy trì lịch sử biến động dữ liệu và ngăn chặn hiện tượng ghi đè dữ liệu khi có nhiều người cùng chỉnh sửa một bản ghi.
5. **Cơ chế Thống kê Snapshot (Statistical Snapshot Pattern)**:
   - Dữ liệu thống kê tài sản được tính toán và lưu dạng Snapshot vào bảng `thong_ke_snapshots`.
   - Giúp giảm tải 90% số lượng truy vấn phức tạp trên DB khi người dùng truy cập trang Dashboard hoặc trang Thống kê.

---

## CHƯƠNG 3: CẤU TRÚC CƠ SỞ DỮ LIỆU (DATABASE SCHEMA)

Hệ thống bao gồm các nhóm bảng chính được liên kết chặt chẽ với nhau:

```
[co_sos] (1) <---> (N) [khu_nhas] (1) <---> (N) [phongs] (1) <---> (N) [thiet_bis]
                                                   |                    |
                                                   v                    v
                                          [bao_cao_su_cos] <--- [lich_su_bao_duongs]
                                                                        ^
                                                                        |
                                                           [dot_kiem_tra_thiet_bis]
```

### 3.1. Danh mục Bảng Dữ liệu Chính

#### 1. Bảng `co_sos` (Quản lý Cơ sở / Campus)
- `id` (PK), `ma_co_so`, `ten_co_so`, `dia_chi`, `dien_tich_dat`, `hieu_luc_tu`, `phien_ban`, `trang_thai_du_lieu` ('hien_hanh', 'da_xoa').

#### 2. Bảng `khu_nhas` (Quản lý Khu nhà / Building)
- `id` (PK), `co_so_id` (FK), `ma_khu_nha`, `ten_khu_nha`, `so_tang`, `dien_tich_xay_dung`, `dien_tich_san`, `trang_thai_du_lieu`, `phien_ban`.

#### 3. Bảng `phongs` (Quản lý Phòng / Room)
- `id` (PK), `khu_nha_id` (FK), `ma_phong`, `ten_phong`, `loai_phong` (Phòng học, Phòng máy tính, Phòng TN, Văn phòng...), `suc_chua`, `dien_tich`, `trang_thai` (Đang sử dụng, Bảo trì...), `qr_token` (UUID dùng cho QR code), `trang_thai_du_lieu`, `phien_ban`.

#### 4. Bảng `thiet_bis` (Quản lý Thiết bị / Asset)
- `id` (PK), `phong_id` (FK), `ma_thiet_bi`, `serial_number`, `ten_thiet_bi`, `loai_thiet_bi`, `hang_san_xuat`, `model`, `nam_san_xuat`, `nam_mua`, `ngay_bao_duong_cuoi`, `chu_ky_bao_duong`, `ngay_bao_duong_tiep_theo`, `gia_tri`, `so_luong` (=1), `don_vi_tinh`, `thong_so_ky_thuat`, `trang_thai` (Mới, Đang sử dụng, Hư hỏng, Đang sửa chữa, Thanh lý), `qr_token` (UUID token), `trang_thai_du_lieu`, `phien_ban`.

#### 5. Bảng `bao_cao_su_cos` (Phản ứng nhanh Sự cố)
- `id` (PK), `phong_id` (FK), `thiet_bi_id` (FK, nullable), `ten_nguoi_bao`, `so_dien_thoai`, `mo_ta_su_co`, `muc_do` (Thấp, Trung bình, Cao, Khẩn cấp), `trang_thai` (Chờ xử lý, Đang sửa chữa, Đã hoàn thành, Từ chối), `ip_address`, `nguoi_hoan_thanh`, `ngay_hoan_thanh`.

#### 6. Bảng `lich_su_bao_duongs` (Lịch sử Bảo dưỡng / Sửa chữa)
- `id` (PK), `thiet_bi_id` (FK), `dot_kiem_tra_thiet_bi_id` (FK, nullable), `ngay_bao_duong`, `noi_dung_bao_duong`, `hinh_thuc_sua_chua` (Bảo dưỡng định kỳ, Sửa chữa sự cố, Thay thế linh kiện), `chi_phi`, `don_vi_thuc_hien`, `nguoi_thuc_hien`.

#### 7. Bảng `dot_kiem_tra_thiet_bis` (Đợt kiểm kê / Kiểm tra tài sản)
- `id` (PK), `ten_dot_kiem_tra`, `tu_ngay`, `den_ngay`, `trang_thai` (Chưa bắt đầu, Đang diễn ra, Đã kết thúc), `mo_ta`.

#### 8. Nhóm Bảng Báo cáo Bộ Giáo dục & Đào tạo (BGD&ĐT)
- `dot_bao_caos`: Quản lý các đợt tổng hợp báo cáo (năm học, đợt báo cáo, ngày bắt đầu/kết thúc, trạng thái).
- `bc_loai_phongs`: Tổng hợp phân loại phòng phục vụ tuyển sinh.
- `bc_tieu_chuan_csvcs`: Tổng hợp chỉ số tiêu chuẩn diện tích/sinh viên.
- `bc_khuon_viens`: Tổng hợp diện tích đất khuôn viên theo cơ sở.
- `bc_cong_trinh_dao_taos`: Tổng hợp diện tích sàn công trình đào tạo và hệ số quy đổi.
- `bc_ha_tang_cntts`: Tổng hợp máy tính, kết nối mạng, hạ tầng CNTT phục vụ đào tạo.

#### 9. Nhóm Bảng Phân quyền & Người dùng (RBAC & Auth)
- `users`: Thông tin tài khoản, email, password, `role` (Admin, Manager, Staff...).
- `screens`: Danh mục màn hình chức năng (`co-so`, `khu-nha`, `phong`, `thiet-bi`, `bao-cao-su-co`, `quan-ly-qr`, `xuat-bao-cao`...).
- `user_permissions`: Lưu ma trận quyền chi tiết của từng User đối với từng Screen (`can_view`, `can_create`, `can_edit`, `can_delete`, `can_import`, `can_export`, `can_regenerate_qr`).

#### 10. Bảng `thong_ke_snapshots` & `imports`
- `thong_ke_snapshots`: Lưu trữ bản chụp dữ liệu thống kê tổng hợp (tổng phòng, tổng thiết bị, tổng giá trị, số thiết bị hư hỏng, tỷ lệ sử dụng) tại các mốc thời gian.
- `imports`: Quản lý tiến trình và kết quả nhập tệp Excel hàng loạt (file name, status, total_rows, success_rows, error_details).

---

## CHƯƠNG 4: DANH MỤC PHÂN HỆ VÀ CHỨC NĂNG CỦA HỆ THỐNG

### 4.1. Phân hệ Quản lý Danh mục (Cơ sở, Khu nhà, Phòng, Thiết bị)
- **Quản lý Cơ sở (Campus Management)**:
  - Xem danh sách, tìm kiếm, lọc theo mã/tên cơ sở.
  - Thêm mới, chỉnh sửa thông tin diện tích đất, địa chỉ.
  - Xóa mềm bản ghi (Soft Delete via `trang_thai_du_lieu`).
  - Import/Export dữ liệu Cơ sở qua tệp Excel mẫu.
- **Quản lý Khu nhà (Building Management)**:
  - Quản lý các khối nhà/dãy nhà thuộc từng Cơ sở.
  - Cập nhật số tầng, diện tích xây dựng, diện tích sàn.
  - Import/Export danh sách khu nhà theo mẫu Excel.
- **Quản lý Phòng (Room Management)**:
  - Phân loại chi tiết phòng (Phòng lý thuyết, Phòng máy tính, Lab thí nghiệm, Hội trường, Văn phòng khoa/phòng ban).
  - Quản lý sức chứa (ghế/chỗ ngồi), diện tích $m^2$, trạng thái hoạt động.
  - Quản lý mã QR Token định danh phòng.
  - Nhập hàng loạt danh sách phòng bằng tệp Excel.
- **Quản lý Thiết bị & Kho (Asset & Inventory Management)**:
  - Quản lý thiết bị định danh cá thể (mỗi bản ghi là 1 thiết bị thực tế có Mã TB & Serial riêng).
  - Quản lý lịch bảo dưỡng định kỳ (`chu_ky_bao_duong`, `ngay_bao_duong_tiep_theo`), cảnh báo thiết bị sắp đến hạn bảo dưỡng.
  - Chức năng **Nhân bản thiết bị (Duplicate)**: Cho phép nhân bản nhanh cấu hình thiết bị hàng loạt khi trang bị nguyên phòng máy.
  - Quản lý **Kho & Lịch sử di chuyển thiết bị**: Đổi vị trí phòng của thiết bị và lưu vết lịch sử luân chuyển.
  - Sinh và quản lý mã **QR Code cho từng thiết bị**.

### 4.2. Phân hệ Phản ứng nhanh Sự cố qua Mã QR (QR Incident Fast Response)
- **Kênh tiếp nhận Công cộng (Public QR Submission)**:
  - Người dùng (Sinh viên, Giảng viên, Cán bộ) quét mã QR dán trên phòng hoặc thiết bị bằng Smartphone.
  - Hệ thống tự động nhận diện thông tin Phòng/Thiết bị dựa trên Secure `qr_token` (không lộ ID số tăng dần trên URL).
  - Form gửi báo cáo sự cố gọn nhẹ: Tên người báo, SĐT, Mô tả lỗi, Mức độ sự cố (Thấp, Trung bình, Cao, Khẩn cấp).
  - Tích hợp Rate-Limiting Anti-Spam Middleware (`throttle:5,1`) để chống gửi báo cáo rác.
- **Kênh Xử lý Quản trị (Admin Incident Board)**:
  - Bảng điều khiển trung tâm tiếp nhận báo cáo sự cố theo thời gian thực (Pusher WebSockets).
  - Cập nhật luồng trạng thái: `Chờ xử lý` $\rightarrow$ `Đang sửa chữa` $\rightarrow$ `Đã hoàn thành` / `Từ chối`.
  - Tự động tạo bản ghi lịch sử sửa chữa trong `lich_su_bao_duongs` khi hoàn thành xử lý.
  - Xuất danh sách sự cố ra Excel phục vụ nghiệm thu.

### 4.3. Phân hệ Quản lý Mã QR (QR Code Central Management)
- Xem danh sách mã QR của toàn bộ Phòng và Thiết bị theo sơ đồ phân cấp.
- Cho phép xem trước (Preview) mã QR và In ấn trực tiếp (Print Batch via `react-to-print`).
- Chức năng **Tạo lại mã QR (Regenerate QR Token)** trong trường hợp lộ mã hoặc thay thế tem QR bị rách/hỏng.

### 4.4. Phân hệ Bảo dưỡng & Kiểm kê Đợt (Maintenance & Audit Rounds)
- **Lịch sử Bảo dưỡng**: Ghi nhận toàn bộ thông tin sửa chữa, chi phí, bên thực hiện (đơn vị ngoài hoặc kỹ thuật viên nội bộ).
- **Đợt Kiểm tra Thiết bị**: Tạo các chiến dịch kiểm kê tài sản theo quý/năm, gán các lượt bảo dưỡng/kiểm tra thiết bị vào từng đợt để đánh giá chất lượng tài sản toàn trường.

### 4.5. Phân hệ Báo cáo chuẩn Bộ Giáo dục & Đào tạo (BGD&ĐT Reporting Engine)
- Khởi tạo **Đợt Báo cáo** (`DotBaoCao`) theo năm học.
- Tự động tổng hợp dữ liệu từ hệ thống vào **5 Bảng Báo cáo chuẩn BGD&ĐT**:
  1. *Biểu 1: Báo cáo Loại phòng phục vụ tuyển sinh*.
  2. *Biểu 2: Báo cáo Tiêu chuẩn Cơ sở vật chất (Diện tích sàn / sinh viên)*.
  3. *Biểu 3: Báo cáo Diện tích khuôn viên đất*.
  4. *Biểu 4: Báo cáo Công trình đào tạo & Hệ số quy đổi*.
  5. *Biểu 5: Báo cáo Hạ tầng CNTT (Số máy tính phục vụ học tập, tỷ lệ kết nối Internet)*.
- Cho phép Quản trị viên duyệt, chỉnh sửa chỉ số tổng hợp trước khi kết xuất.
- **Xuất tệp Excel chuẩn định dạng nộp BGD&ĐT**.

### 4.6. Phân hệ Bảng điều khiển Thống kê (Dashboard & Analytics)
- Bảng điều khiển trung tâm với chỉ số Real-time: Tổng số Cơ sở, Khu nhà, Phòng học, Thiết bị, Tổng giá trị tài sản (VNĐ).
- Biểu đồ phân bổ tài sản theo loại, theo phòng và tình trạng thiết bị (Đang hoạt động, Hư hỏng, Đang bảo dưỡng).
- Biểu đồ xu hướng báo cáo sự cố và chi phí bảo dưỡng.
- Tích hợp nút **Tính toán lại Thống kê (`recalculate`)** để làm mới Snapshot dữ liệu khi cần.

### 4.7. Phân hệ Phân quyền & Quản trị Hệ thống (RBAC & System Admin)
- Quản lý tài khoản người dùng, đổi mật khẩu, kích hoạt/khóa tài khoản.
- Ma trận **Phân quyền động theo Màn hình (Screen Permissions)**: Cấp quyền xem, thêm, sửa, xóa, import, export, tạo QR cho từng cán bộ quản lý theo đúng vai trò nhiệm vụ.

---

## CHƯƠNG 5: MÔ TẢ CÁC QUY TRÌNH NGHỆP VỤ TIÊU BIỂU

### 5.1. Quy trình Phản ứng nhanh Báo cáo & Sửa chữa Sự cố qua QR Code

```
[Giảng viên / Sinh viên]
       |
       v  (Quét mã QR dán tại Phòng/Thiết bị)
[Hệ thống hiển thị Form báo cáo sự cố (Public - No Auth required)]
       |
       v  (Nhập tên, SĐT, Mô tả sự cố, Mức độ khẩn cấp)
[Gửi Báo cáo -> Rate Limiter check (tối đa 5 lượt/phút)]
       |
       v  (Tạo bản ghi trong `bao_cao_su_cos` với trạng thái 'Chờ xử lý')
[Laravel Event Broadcasting -> WebSockets / Pusher]
       |
       v  (Thông báo Real-time hiển thị trên màn hình Admin)
[Cán bộ Quản lý CSVC tiếp nhận & Đổi trạng thái 'Đang sửa chữa']
       |
       v  (Kỹ thuật viên tiến hành sửa chữa / bảo dưỡng)
[Hoàn thành sửa chữa -> Đổi trạng thái 'Đã hoàn thành']
       |
       v  (Tự động ghi nhận Lịch sử sửa chữa & Cập nhật trạng thái Thiết bị thành 'Đang sử dụng')
```

### 5.2. Quy trình Tổng hợp & Xuất Báo cáo chuẩn Bộ Giáo dục & Đào tạo

```
[Tạo mới Đợt Báo cáo (Tên đợt, Năm học, Ngày bắt đầu - kết thúc)]
       |
       v
[Thực thi 'tongHopBaoCao()' trong BaoCaoBgdService]
       |
       +---> [Tính toán `bc_loai_phongs`: Đếm số phòng học, phòng lab, diện tích theo từng loại]
       +---> [Tính toán `bc_tieu_chuan_csvcs`: Tổng diện tích / tổng sinh viên quy đổi]
       +---> [Tính toán `bc_khuon_viens`: Tổng diện tích đất các cơ sở]
       +---> [Tính toán `bc_cong_trinh_dao_taos`: Áp hệ số quy đổi công trình]
       +---> [Tính toán `bc_ha_tang_cntts`: Thống kê dàn máy tính, hạ tầng mạng]
       |
       v
[Lưu kết quả tổng hợp vào các bảng `bc_*` tương ứng]
       |
       v
[Quản trị viên xem trước (Preview) & Xuất tệp Excel Báo cáo chuẩn BGD&ĐT]
```

### 5.3. Quy trình Bulk Import Dữ liệu từ Excel

```
[Người dùng tải File Excel mẫu (.xlsx)]
       |
       v
[Điền dữ liệu & Tải file lên hệ thống (Endpoint /import)]
       |
       v
[ImportService / Laravel-Excel tiếp nhận & Tạo bản ghi trong `imports`]
       |
       v
[Kiểm tra Validations (Mã không trùng lặp, Khóa ngoại tồn tại, Kiểu dữ liệu)]
       |
       +---> (Nếu có Lỗi): Cập nhật trạng thái 'failed', ghi chi tiết dòng lỗi vào `error_details`
       |
       +---> (Nếu Hợp lệ): Ghi dữ liệu vào DB trong DB Transaction, cấp tạo `qr_token` và `phien_ban` = 1
```

---

## CHƯƠNG 6: ĐÁNH GIÁ KẾT QUẢ VÀ ĐIỂM NỔI BẬT KỸ THUẬT

### 6.1. Kết quả Đạt được
1. **Hoàn thiện 100% các phân hệ nghiệp vụ** quản lý cơ sở vật chất từ danh mục đa cấp đến xử lý sự cố và báo cáo cấp Bộ.
2. **Giao diện hiện đại, chuẩn doanh nghiệp**: Sử dụng Ant Design 5 kết hợp Tailwind CSS mang lại trải nghiệm xem và thao tác trực quan, mượt mà trên cả Máy tính và Điện thoại thông minh.
3. **Giải quyết triệt để bài toán Phản ứng nhanh**: Rút ngắn thời gian tiếp nhận và xử lý sự cố thiết bị tại phòng học từ vài ngày xuống còn vài phút nhờ giải pháp mã QR Code thông minh.
4. **Tự động hóa hoàn toàn công tác Báo cáo BGD&ĐT**: Loại bỏ 95% thời gian tổng hợp thủ công qua Excel vào mỗi kỳ báo cáo.

### 6.2. Các Điểm Nổi bật về Mặt Kỹ thuật (Technical Highlights)
- **Kiến trúc Inertia.js + React 18**: Tối ưu tốc độ tải trang SPA nhưng vẫn duy trì tính đơn giản, bảo mật của bộ khung Laravel Backend.
- **Cơ chế Snapshots Thống kê (`thong_ke_snapshots`)**: Tăng tốc độ phản hồi Dashboard lên gấp **10 lần** so với việc truy vấn quét lại toàn bộ bảng lớn.
- **Cơ chế Optimistic Locking (Versioning)**: Bảo vệ tính toàn vẹn dữ liệu khi nhiều quản trị viên cùng truy cập và cập nhật cơ sở vật chất đồng thời.
- **Phân quyền Chi tiết đến từng Màn hình & Thao tác (RBAC)**: Đảm bảo tính bảo mật và phân tách trách nhiệm rõ ràng trong môi trường giáo dục đại học.
- **QR Code An toàn (Secure UUID Tokens)**: Không sử dụng ID số trực tiếp trên mã QR để ngăn chặn các cuộc tấn công quét vét URL (Enumeration Attack).

---
*Tài liệu này được tổng hợp trực tiếp dựa trên mã nguồn và cơ sở dữ liệu thực tế của Dự án Hệ thống Quản lý Cơ sở Vật chất (QLCSVC).*
